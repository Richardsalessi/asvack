<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orden;

class EnvioController extends Controller
{
    public function configurar(Orden $orden, Request $request)
    {
        // TODO: lógica real
        return response()->json(['ok' => true, 'msg' => 'Configurar envío pendiente'], 200);
    }

    public function cambiarEstado(Orden $orden, Request $request)
    {
        // TODO: lógica real
        return response()->json(['ok' => true, 'msg' => 'Cambiar estado de envío pendiente'], 200);
    }
}

