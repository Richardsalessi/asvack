<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarifaEnvio;
use Illuminate\Http\Request;

class TarifaEnvioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $tarifas = TarifaEnvio::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('ciudad', 'like', "%{$q}%");
            })
            // Ordena solo por ciudad y, como desempate, por costo
            ->orderBy('ciudad', 'asc')
            ->orderBy('costo', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tarifas.index', compact('tarifas', 'q'));
    }

    public function create()
    {
        return view('admin.tarifas.form', ['tarifa' => new TarifaEnvio()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ciudad'          => 'required|string|max:120',
            'costo'           => 'required|integer|min:0',
            'tiempo_estimado' => 'nullable|string|max:60',
            // el checkbox puede no venir cuando está desmarcado
            'activo'          => 'sometimes|boolean',
        ]);

        // 1 si viene marcado; 0 si no viene
        $data['activo'] = $request->boolean('activo');

        TarifaEnvio::create($data);

        // 🔔 subir versión de tarifas
        cache()->forever('tarifas_version', now()->timestamp);

        return redirect()
            ->route('admin.tarifas.index')
            ->with('success', 'Tarifa creada');
    }

    public function edit(TarifaEnvio $tarifas_envio)
    {
        return view('admin.tarifas.form', ['tarifa' => $tarifas_envio]);
    }

    public function update(Request $request, TarifaEnvio $tarifas_envio)
    {
        $data = $request->validate([
            'ciudad'          => 'required|string|max:120',
            'costo'           => 'required|integer|min:0',
            'tiempo_estimado' => 'nullable|string|max:60',
            'activo'          => 'sometimes|boolean',
        ]);

        // 1 si viene; 0 si no viene
        $data['activo'] = $request->boolean('activo');

        $tarifas_envio->update($data);

        // 🔔 subir versión de tarifas
        cache()->forever('tarifas_version', now()->timestamp);

        return redirect()
            ->route('admin.tarifas.index')
            ->with('success', 'Tarifa actualizada');
    }

    public function destroy(TarifaEnvio $tarifas_envio)
    {
        $tarifas_envio->delete();

        // 🔔 subir versión de tarifas
        cache()->forever('tarifas_version', now()->timestamp);

        return back()->with('success', 'Tarifa eliminada');
    }
}
