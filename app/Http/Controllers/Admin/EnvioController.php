<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orden;

class EnvioController extends Controller
{
    /**
     * Guardar/actualizar datos de transporte (transportadora, guía, etc.)
     */
    public function configurar(Request $request, Orden $orden)
    {
        // La ruta ya está protegida con can:admin-access
        $data = $request->validate([
            'transportadora' => 'nullable|string|max:120',
            'numero_guia'    => 'nullable|string|max:120',
            'tipo_envio'     => 'required|string|in:pagado_cliente,asumido_empresa,contraentrega',
            'costo_envio'    => 'nullable|numeric|min:0',
            'notas'          => 'nullable|string|max:1000',
        ]);

        // Crea o actualiza el registro 1:1 (hasOne) de envío
        $orden->envioRegistro()->updateOrCreate(
            [], // por ser hasOne no necesitamos más condiciones
            [
                'transportadora' => $data['transportadora'] ?? null,
                'numero_guia'    => $data['numero_guia'] ?? null,
                'tipo_envio'     => $data['tipo_envio'],
                'costo_envio'    => $data['costo_envio'] ?? 0,
                'notas'          => $data['notas'] ?? null,
                // si no existe, déjalo "pendiente" por defecto
                'estado_envio'   => $orden->envioRegistro->estado_envio ?? 'pendiente',
            ]
        );

        return redirect()
            ->route('ordenes.show', $orden)
            ->with('success', 'Datos de envío guardados correctamente.');
    }

    /**
     * Cambiar estado del envío (pendiente, en tránsito, entregado, devuelto)
     */
    public function cambiarEstado(Request $request, Orden $orden)
    {
        // La ruta ya está protegida con can:admin-access
        $data = $request->validate([
            'estado_envio' => 'required|in:pendiente,en_transito,entregado,devuelto',
        ]);

        $orden->envioRegistro()->updateOrCreate([], [
            'estado_envio' => $data['estado_envio'],
        ]);

        return redirect()
            ->route('ordenes.show', $orden)
            ->with('success', 'Estado de envío actualizado.');
    }
}
