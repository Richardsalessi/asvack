<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Mensaje;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizacionesPendientes = Cotizacion::where('estado', 'pendiente')->with('cliente', 'proveedor')->get();
        $cotizacionesEnProceso = Cotizacion::where('estado', 'en_proceso')->with('cliente', 'proveedor')->get();
        $cotizacionesFinalizadas = Cotizacion::where('estado', 'finalizado')->with('cliente', 'proveedor')->get();

        return view('provider.cotizaciones.index', compact('cotizacionesPendientes', 'cotizacionesEnProceso', 'cotizacionesFinalizadas'));
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with('mensajes', 'cliente', 'proveedor')->findOrFail($id);

        return response()->json([
            'id' => $cotizacion->id,
            'cliente' => $cotizacion->cliente->name,
            'proveedor' => $cotizacion->proveedor->name ?? 'Proveedor',
            'detalle' => $cotizacion->detalle,
            'estado' => $cotizacion->estado,
            'fecha' => Carbon::parse($cotizacion->created_at)->timezone('America/Bogota')->format('d-m-Y H:i'),
            'mensajes' => $cotizacion->mensajes->map(function ($mensaje) use ($cotizacion) {
                return [
                    'contenido' => $mensaje->contenido,
                    'es_proveedor' => $mensaje->es_proveedor,
                    'nombre' => $mensaje->es_proveedor ? ($cotizacion->proveedor->name ?? 'Proveedor') : $cotizacion->cliente->name,
                    'created_at' => Carbon::parse($mensaje->created_at)->timezone('America/Bogota')->toIso8601String(),
                ];
            }),
        ]);
    }

    public function responder(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string|max:255',
        ]);

        $cotizacion = Cotizacion::with('proveedor')->findOrFail($id);

        $mensaje = Mensaje::create([
            'cotizacion_id' => $id,
            'contenido' => $request->input('mensaje'),
            'es_proveedor' => true,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => [
                'contenido' => $mensaje->contenido,
                'es_proveedor' => $mensaje->es_proveedor,
                'nombre' => $cotizacion->proveedor->name ?? 'Proveedor',
                'created_at' => Carbon::parse($mensaje->created_at)->timezone('America/Bogota')->toIso8601String(),
            ],
        ]);
    }

    public function marcarEnProceso($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->estado = 'en_proceso';
        $cotizacion->save();

        return response()->json([
            'success' => true,
            'estado' => 'en_proceso',
        ]);
    }

    public function marcarFinalizado($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->estado = 'finalizado';
        $cotizacion->save();

        return response()->json([
            'success' => true,
            'estado' => 'finalizado',
        ]);
    }
}
