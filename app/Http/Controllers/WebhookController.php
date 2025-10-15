<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Orden;
use App\Models\Producto;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('[ePayco] Webhook hit', [
            'ip'      => $request->ip(),
            'payload' => $request->all(),
        ]);

        // ===== Firma ePayco (SHA-256) =====
        $custId   = (string) env('EPAYCO_P_CUST_ID_CLIENTE');
        $pkey     = (string) env('EPAYCO_P_KEY'); // ¡OJO! es P_KEY, no PRIVATE_KEY
        $refPayco = (string) $request->input('x_ref_payco', '');
        $trxId    = (string) $request->input('x_transaction_id', '');

        // ePayco firma con el monto EXACTO que envía en x_amount (sin formatear).
        $amountRaw = (string) ($request->input('x_amount') ?? '');
        // quitar espacios y asegurar punto como separador decimal si viniera
        $amount = trim($amountRaw);
        $currency = (string) $request->input('x_currency_code', 'COP');

        // Firma recibida
        $recvSig = (string) $request->input('x_signature', '');

        // Firma esperada (SHA-256)
        $baseString = $custId.'^'.$pkey.'^'.$refPayco.'^'.$trxId.'^'.$amount.'^'.$currency;
        $calcSigSha256 = hash('sha256', $baseString);

        // (Compatibilidad: algunos ambientes antiguos de ePayco han usado MD5.
        //  Si quieres, acepta MD5 como fallback, pero NO es necesario normalmente)
        $calcSigMd5 = md5($baseString);

        if (!$recvSig || !(hash_equals($recvSig, $calcSigSha256) || hash_equals($recvSig, $calcSigMd5))) {
            Log::warning('[ePayco] Firma inválida', [
                'recvSig'   => $recvSig,
                'calcSha256'=> $calcSigSha256,
                'calcMd5'   => $calcSigMd5,
                'base'      => $baseString,
                'amountRaw' => $amountRaw,
            ]);
            return response('OK', 200); // devolvemos 200 para evitar reintentos en tormenta
        }

        // ====== ID de orden (extra1) ======
        $ordenId = (int) ($request->input('x_extra1') ?? $request->input('extra1') ?? 0);
        if (!$ordenId) {
            Log::warning('[ePayco] Webhook sin x_extra1/extra1');
            return response('OK', 200);
        }

        // ====== Estado reportado ======
        $estadoTxt = mb_strtolower((string) (
            $request->input('x_response')
            ?? $request->input('response')
            ?? $request->input('x_transaction_state')
            ?? ''
        ), 'UTF-8');

        $codResp   = (int) $request->input('x_cod_response', 0); // 1 = Aprobada

        $aprobada  = $codResp === 1 || in_array($estadoTxt, ['aprobada','aceptada'], true);
        $rechazada = in_array($estadoTxt, ['rechazada','fallida'], true);
        $cancelada = $estadoTxt === 'cancelada';
        $pendiente = $estadoTxt === 'pendiente';

        DB::transaction(function () use ($ordenId, $aprobada, $rechazada, $cancelada, $pendiente, $request, $trxId, $refPayco) {

            /** @var \App\Models\Orden|null $orden */
            $orden = Orden::with('detalles')->lockForUpdate()->find($ordenId);
            if (!$orden) {
                Log::warning("[ePayco] Orden {$ordenId} no encontrada");
                return;
            }

            $estadoAnterior = (string) $orden->estado;

            // Guardamos huella SIEMPRE (si tu columna es json -> cast en el modelo)
            $orden->payload    = $request->all();
            $orden->trx_id     = $trxId ?: $orden->trx_id;
            $orden->ref_epayco = $refPayco ?: $orden->ref_epayco;

            // ===== Transiciones idempotentes =====

            // → PAGADA (solo si venía 'pendiente') => descontar stock
            if ($aprobada && $estadoAnterior === 'pendiente') {
                foreach ($orden->detalles as $det) {
                    $p = Producto::lockForUpdate()->find($det->producto_id);
                    if (!$p) continue;
                    $p->stock = max(0, (int) $p->stock - (int) $det->cantidad);
                    $p->save();
                }

                $orden->estado    = 'pagada';
                $orden->respuesta = 'Aprobada';
                $orden->save();

                // Crear registro de envío si tu relación existe
                if (method_exists($orden, 'envioRegistro')) {
                    $orden->envioRegistro()->firstOrCreate(
                        ['orden_id' => $orden->id],
                        [
                            'estado_envio' => 'pendiente',
                            'tipo_envio'   => 'pagado_cliente',
                            'fecha_envio'  => now(),
                            'notas'        => 'Envío pendiente de asignación de transportadora.',
                        ]
                    );
                } elseif (method_exists($orden, 'envio')) {
                    $orden->envio()->firstOrCreate(
                        ['orden_id' => $orden->id],
                        [
                            'estado_envio' => 'pendiente',
                            'tipo_envio'   => 'pagado_cliente',
                            'fecha_envio'  => now(),
                            'notas'        => 'Envío pendiente de asignación de transportadora.',
                        ]
                    );
                }

                Log::info("[ePayco] Orden {$orden->id} → PAGADA (stock descontado)");
            }
            // → RECHAZADA/CANCELADA si ya estaba pagada => reponer stock
            elseif (($rechazada || $cancelada) && $estadoAnterior === 'pagada') {
                foreach ($orden->detalles as $det) {
                    $p = Producto::lockForUpdate()->find($det->producto_id);
                    if (!$p) continue;
                    $p->stock = (int) $p->stock + (int) $det->cantidad;
                    $p->save();
                }

                $orden->estado    = $rechazada ? 'rechazada' : 'cancelada';
                $orden->respuesta = $rechazada ? 'Rechazada' : 'Cancelada';
                $orden->save();

                Log::info("[ePayco] Orden {$orden->id} → {$orden->estado} (stock repuesto)");
            }
            // → Pendiente: solo huella
            elseif ($pendiente) {
                $orden->respuesta = 'Pendiente';
                $orden->save();
                Log::info("[ePayco] Orden {$orden->id} permanece PENDIENTE");
            }
            // → Rechazada/Cancelada estando pendiente: marcar sin tocar stock
            elseif ($rechazada || $cancelada) {
                $orden->estado    = $rechazada ? 'rechazada' : 'cancelada';
                $orden->respuesta = $rechazada ? 'Rechazada' : 'Cancelada';
                $orden->save();
                Log::info("[ePayco] Orden {$orden->id} → {$orden->estado} (sin tocar stock)");
            }
            // → Cualquier otro texto: solo guardar
            else {
                $orden->respuesta = $estadoTxt ?: ($orden->respuesta ?? 'Pendiente');
                $orden->save();
                Log::info("[ePayco] Orden {$orden->id} estado no mapeado: '{$estadoTxt}'");
            }
        });

        // Siempre 200
        return response('OK', 200);
    }
}
