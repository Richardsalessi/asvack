<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Envio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdenController extends Controller
{
    /**
     * Historial de compras del cliente autenticado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Orden::with(['detalles.producto', 'envioRegistro'])
            ->where('user_id', $user->id)
            ->latest();

        // Filtro opcional por estado (pendiente, pagada, rechazada, cancelada, enviada, entregada)
        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', $estado);
        }

        $ordenes = $query->paginate(10)->withQueryString();

        return view('ordenes.index', compact('ordenes'));
    }

    /**
     * Mostrar detalle de una orden específica (cliente dueño o admin).
     */
    public function show(Orden $orden)
    {
        $orden->load(['detalles.producto.imagenes', 'envioRegistro', 'user']);

        $user    = Auth::user();
        $esAdmin = $user && $user->can('admin-access');

        if (!$esAdmin && (int) $orden->user_id !== (int) $user->id) {
            abort(403);
        }

        // Usa la vista según rol (admin/cliente)
        return view($esAdmin ? 'admin.ordenes.show' : 'ordenes.show', compact('orden'));
    }

    /**
     * Vista admin de ventas/pedidos con filtros.
     * GET /admin/ventas  (middleware: can:admin-access)
     */
    public function admin(Request $request)
    {
        // Filtros de la vista
        $q           = trim($request->input('q', ''));        // #orden, ref_epayco, ultimo_invoice, guía, transportadora
        $estado      = $request->input('estado', '');         // pendiente, pagada, rechazada, cancelada, enviada, entregada
        $estadoEnvio = $request->input('estado_envio', '');   // pendiente, en_transito, entregado, devuelto
        $desde       = $request->input('desde');              // YYYY-MM-DD
        $hasta       = $request->input('hasta');              // YYYY-MM-DD

        // Listado principal
        $ordenes = Orden::with(['user', 'envioRegistro', 'detalles.producto'])
            ->when($estado, fn ($q1) => $q1->where('estado', $estado))
            ->when($estadoEnvio, fn ($q2) => $q2->whereHas('envioRegistro', fn ($qe) => $qe->where('estado_envio', $estadoEnvio)))
            ->when($q, function ($q3) use ($q) {
                $q3->where(function ($w) use ($q) {
                    $w->where('id', (int) $q)
                      ->orWhere('ref_epayco', 'like', "%{$q}%")
                      ->orWhere('ultimo_invoice', 'like', "%{$q}%")
                      ->orWhereHas('envioRegistro', function ($qe) use ($q) {
                          $qe->where('numero_guia', 'like', "%{$q}%")
                             ->orWhere('transportadora', 'like', "%{$q}%");
                      });
                });
            })
            ->when($desde, fn ($qq) => $qq->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($qq) => $qq->whereDate('created_at', '<=', $hasta))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // ===== Tarjetas separadas =====
        // Base por fechas para ÓRDENES (pagos)
        $baseOrdenes = Orden::query()
            ->when($desde, fn ($qq) => $qq->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($qq) => $qq->whereDate('created_at', '<=', $hasta));

        $statsPedidos = [
            'pendientes' => (clone $baseOrdenes)->where('estado', 'pendiente')->count(),
            'pagadas'    => (clone $baseOrdenes)->where('estado', 'pagada')->count(),
            'rechazadas' => (clone $baseOrdenes)->where('estado', 'rechazada')->count(),
            'canceladas' => (clone $baseOrdenes)->where('estado', 'cancelada')->count(),
        ];

        // Base por fechas para ENVÍOS (logística)
        $baseEnvios = Envio::query()
            ->when($desde, fn ($qq) => $qq->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($qq) => $qq->whereDate('created_at', '<=', $hasta));

        $statsEnvios = [
            'pendientes'  => (clone $baseEnvios)->where('estado_envio', 'pendiente')->count(),
            'en_transito' => (clone $baseEnvios)->where('estado_envio', 'en_transito')->count(),
            'entregados'  => (clone $baseEnvios)->where('estado_envio', 'entregado')->count(),
            'devueltos'   => (clone $baseEnvios)->where('estado_envio', 'devuelto')->count(),
            // Pagadas sin registro de envío todavía
            'sin_crear'   => (clone $baseOrdenes)
                                ->where('estado', 'pagada')
                                ->whereDoesntHave('envioRegistro')
                                ->count(),
        ];

        return view('admin.ordenes.index', compact(
            'ordenes', 'estado', 'estadoEnvio', 'q', 'desde', 'hasta',
            'statsPedidos', 'statsEnvios'
        ));
    }

    /**
     * Dashboard Admin: estadísticas rápidas.
     * GET /admin/dashboard  (middleware: can:admin-access)
     */
    public function adminDashboard()
    {
        $stats = [
            'pendientes'  => Orden::where('estado', 'pendiente')->count(),
            'pagados'     => Orden::where('estado', 'pagada')->count(),
            'en_transito' => Envio::where('estado_envio', 'en_transito')->count(),
            'entregados'  => Envio::where('estado_envio', 'entregado')->count(),
            'devueltos'   => Envio::where('estado_envio', 'devuelto')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
