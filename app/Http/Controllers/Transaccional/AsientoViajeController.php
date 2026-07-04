<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\AsientoViaje;
use App\Models\Viaje;
use Illuminate\Http\Request;

class AsientoViajeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('asientoviaje.ver');

        $viajes = Viaje::with('ruta', 'bus')
                        ->where('estado_base', 1)
                        ->when($request->search, function ($query, $search) {
                            $query->whereHas('ruta', function ($q) use ($search) {
                                $q->where('origen', 'like', "%{$search}%")
                                  ->orWhere('destino', 'like', "%{$search}%");
                            });
                        })
                        ->paginate(10)
                        ->withQueryString();

        return view('transaccional.asientoviaje.index', compact('viajes'));
    }

    public function create()
    {
        // No se usa: los asientos se generan automáticamente al crear un Viaje.
        return redirect()->route('transaccional.asientoviaje.index');
    }

    public function store(Request $request)
    {
        // No se usa: los asientos se generan automáticamente al crear un Viaje.
        return redirect()->route('transaccional.asientoviaje.index');
    }

    public function show(Viaje $asientoviaje)
{
    $this->authorize('asientoviaje.ver');

    $viaje = $asientoviaje;
    $viaje->load('ruta', 'bus');

    $asientos = AsientoViaje::where('viaje_id', $viaje->id)
                             ->where('estado_base', 1)
                             ->orderBy('numero_asiento')
                             ->get();

    return view('transaccional.asientoviaje.show', compact('viaje', 'asientos'));
}

    public function edit(AsientoViaje $asientoviaje)
{
    $this->authorize('asientoviaje.editar');
    $asientoviaje->load('viaje.ruta');
    return view('transaccional.asientoviaje.edit', compact('asientoviaje'));
}

public function update(Request $request, AsientoViaje $asientoviaje)
{
    $this->authorize('asientoviaje.editar');

    $request->validate([
        'estado' => 'required|in:disponible,ocupado,bloqueado',
    ]);

    $asientoviaje->update(['estado' => $request->estado]);

    return redirect()->route('transaccional.asientoviaje.show', $asientoviaje->viaje_id)
                     ->with('success', 'Estado del asiento actualizado correctamente.');
}

    public function destroy(AsientoViaje $asiento)
    {
        // No se usa: los asientos no se eliminan individualmente, solo se bloquean.
        return redirect()->route('transaccional.asientoviaje.index');
    }
}