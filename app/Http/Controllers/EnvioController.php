<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function configurar(Orden $orden, Request $r)
    {
        $data = $r->validate([
            'transportadora' => 'nullable|string|max:50',
            'numero_guia'    => 'nullable|string|max:100',
            'tipo_envio'     => 'required|in:pagado_cliente,asumido_empresa,contraentrega',
            'costo_envio'    => 'nullable|integer|min:0',
            'notas'          => 'nullable|string',
        ]);

        // ⬇️ was: $orden->envio()->updateOrCreate([], $data);
        $orden->envioRegistro()->updateOrCreate(['orden_id' => $orden->id], $data);

        return back()->with('success', 'Datos de envío guardados.');
    }

    public function cambiarEstado(Orden $orden, Request $r)
    {
        $r->validate([
            'estado_envio' => 'required|in:pendiente,en_transito,entregado,devuelto',
        ]);

        $envio = $orden->envioRegistro; // ⬅️ obtener registro
        if ($envio) {
            $envio->update([
                'estado_envio' => $r->estado_envio,
                'fecha_envio'  => $r->estado_envio === 'en_transito'
                    ? now()
                    : ($envio->fecha_envio),
            ]);
        }

        // (Opcional) sincronizar estado general de la orden
        if ($r->estado_envio === 'en_transito' && $orden->estado !== 'enviada') {
            $orden->update(['estado' => 'enviada']);
        }
        if ($r->estado_envio === 'entregado' && $orden->estado !== 'entregada') {
            $orden->update(['estado' => 'entregada']);
        }

        return back()->with('success', 'Estado de envío actualizado.');
    }
}
