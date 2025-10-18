<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarifaEnvio;
use Illuminate\Http\Request;

class TarifaEnvioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $tarifas = TarifaEnvio::when($q, function($qq) use ($q){
                $qq->where('ciudad','like',"%{$q}%")
                   ->orWhere('barrio','like',"%{$q}%");
            })
            ->orderBy('ciudad')->orderBy('barrio')
            ->paginate(20)->withQueryString();

        return view('admin.tarifas.index', compact('tarifas','q'));
    }

    public function create()
    {
        return view('admin.tarifas.form', ['tarifa' => new TarifaEnvio()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ciudad' => 'required|string|max:120',
            'barrio' => 'nullable|string|max:120',
            'costo'  => 'required|integer|min:0',
            'activo' => 'boolean',
            'tiempo_estimado' => 'nullable|string|max:60',
        ]);
        $data['activo'] = (bool)($data['activo'] ?? true);
        TarifaEnvio::create($data);

        return redirect()->route('admin.tarifas.index')->with('success','Tarifa creada');
    }

    public function edit(TarifaEnvio $tarifas_envio)
    {
        return view('admin.tarifas.form', ['tarifa' => $tarifas_envio]);
    }

    public function update(Request $request, TarifaEnvio $tarifas_envio)
    {
        $data = $request->validate([
            'ciudad' => 'required|string|max:120',
            'barrio' => 'nullable|string|max:120',
            'costo'  => 'required|integer|min:0',
            'activo' => 'boolean',
            'tiempo_estimado' => 'nullable|string|max:60',
        ]);
        $data['activo'] = (bool)($data['activo'] ?? true);
        $tarifas_envio->update($data);

        return redirect()->route('admin.tarifas.index')->with('success','Tarifa actualizada');
    }

    public function destroy(TarifaEnvio $tarifas_envio)
    {
        $tarifas_envio->delete();
        return back()->with('success','Tarifa eliminada');
    }
}
