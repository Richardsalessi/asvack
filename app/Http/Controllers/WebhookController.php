<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Envio; // <-- añadido

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Tomamos campos clave (ePayco puede variar nombres)
        $estado   = strtolower((string)($request->input('x_response') ?? $request->input('response') ?? '')); // "aprobada", "rechazada", etc.
        $codResp  = (int) $request->input('x_cod_response', 0);  // 1 = aprobada (ePayco)
        $trxId    = $request->input('x_transaction_id');
        $refPayco = $request->input('x_ref_payco');

        // Referencia de orden: enviamos el ID en extra1 desde tu checkout
        $ordenId  = (int) ($request->input('x_extra1') ?? $request->input('x_id_invoice') ?? $request->input('invoice') ?? 0);
        if (!$ordenId) {
            // Respondemos 200 para no reintentos infinitos, pero no hacemos nada
            return response('Sin referencia de orden', 200);
        }

        // 2) Procesamos atómicamente y protegidos contra carreras
        DB::transaction(function () use ($ordenId, $estado, $codResp, $trxId, $refPayco, $request) {
            // Bloquear fila de orden durante la transacción
            $orden = Orden::with('detalles')
                ->lockForUpdate()
                ->find($ordenId);

            if (!$orden) {
                return; // nada que hacer
            }

            // Si ya no está pendiente, solo guarda huella y sal
            if ($orden->estado !== 'pendiente') {
                $orden->payload    = $request->all();
                $orden->trx_id     = $trxId ?: $orden->trx_id;
                $orden->ref_epayco = $refPayco ?: $orden->ref_epayco;
                $orden->save();
                return;
            }

            // Guardamos siempre el payload bruto + IDs
            $orden->payload    = $request->all();
            $orden->trx_id     = $trxId;
            $orden->ref_epayco = $refPayco;

            // 3) Decidir estado de forma simple
            $aprobada  = ($codResp === 1) || in_array($estado, ['aprobada', 'aceptada'], true);
            $rechazada = in_array($estado, ['rechazada', 'fallida'], true);
            $cancelada = ($estado === 'cancelada');

            if ($aprobada) {
                // 4) Descontar stock de forma segura
                foreach ($orden->detalles as $det) {
                    /** @var Producto|null $p */
                    $p = Producto::lockForUpdate()->find($det->producto_id);
                    if (!$p) continue;

                    $p->stock = max(0, (int)$p->stock - (int)$det->cantidad);
                    $p->save();
                }

                // 5) Actualizar estado de la orden
                $orden->estado    = 'pagada';
                $orden->respuesta = 'Aprobada';
                $orden->save();

                // 6) Crear registro de envío si no existe (queda 'pendiente')
                if (!$orden->envio) {
                    Envio::create([
                        'orden_id'     => $orden->id,
                        'estado_envio' => 'pendiente',
                        'tipo_envio'   => 'pagado_cliente',
                        'fecha_envio'  => now(),
                        'notas'        => 'Envío pendiente de asignación de transportadora.',
                    ]);
                }
            } elseif ($rechazada) {
                $orden->estado    = 'rechazada';
                $orden->respuesta = 'Rechazada';
                $orden->save();
            } elseif ($cancelada) {
                $orden->estado    = 'cancelada';
                $orden->respuesta = 'Cancelada';
                $orden->save();
            } else {
                // otros estados de ePayco: pendiente, etc.
                $orden->respuesta = $request->input('x_response') ?? $orden->respuesta;
                $orden->save();
            }
        });

        // ePayco solo necesita un 200 OK
        return response('OK', 200);
    }
}
