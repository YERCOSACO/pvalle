<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\AsignacionConductor;
use App\Models\Viaje;
use App\Models\Conductor;
use Illuminate\Http\Request;

class AsignacionConductorController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('asignacionconductor.ver');

    $viajes = Viaje::with('ruta', 'bus', 'asignaciones.conductor')
                    ->where('estado_base', 1)
                    ->whereHas('asignaciones')
                    ->when($request->search, function ($query, $search) {
                        $query->whereHas('ruta', function ($q) use ($search) {
                            $q->where('origen', 'like', "%{$search}%")
                              ->orWhere('destino', 'like', "%{$search}%");
                        });
                    })
                    ->paginate(10)
                    ->withQueryString();

    return view('transaccional.asignacionconductor.index', compact('viajes'));
    }

public function create()
{
    $this->authorize('asignacionconductor.crear');

    $viajes = Viaje::where('estado_base', 1)
                    ->whereDoesntHave('asignaciones', function ($q) {
                        $q->where('tipo_asignacion', 'Principal');
                    })
                    ->get();

    $conductores = Conductor::where('estado_base', 1)->get();

    return view('transaccional.asignacionconductor.create', compact('viajes', 'conductores'));
}

public function store(Request $request)
{
    $this->authorize('asignacionconductor.crear');

    $request->validate([
        'viaje_id'             => 'required|exists:viajes,id',
        'conductor_principal'  => 'required|exists:conductores,id',
        'conductor_relevo'     => 'required|exists:conductores,id|different:conductor_principal',
        'ayudante'             => 'nullable|exists:conductores,id',
    ]);

    $yaExiste = AsignacionConductor::where('viaje_id', $request->viaje_id)
                                    ->where('tipo_asignacion', 'Principal')
                                    ->exists();

    if ($yaExiste) {
        return back()->withErrors(['viaje_id' => 'Este viaje ya tiene conductores asignados.']);
    }

    AsignacionConductor::create([
        'viaje_id'        => $request->viaje_id,
        'conductor_id'    => $request->conductor_principal,
        'tipo_asignacion' => 'Principal',
    ]);

    AsignacionConductor::create([
        'viaje_id'        => $request->viaje_id,
        'conductor_id'    => $request->conductor_relevo,
        'tipo_asignacion' => 'Relevo',
    ]);

    if ($request->filled('ayudante')) {
        AsignacionConductor::create([
            'viaje_id'        => $request->viaje_id,
            'conductor_id'    => $request->ayudante,
            'tipo_asignacion' => 'Ayudante',
        ]);
    }

    return redirect()->route('transaccional.asignacionconductor.index')
                     ->with('success', 'Conductores asignados correctamente.');
}

    public function edit(Viaje $viaje)
{
    $this->authorize('asignacionconductor.editar');

    $viaje->load('asignaciones.conductor');
    $conductores = Conductor::where('estado_base', 1)->get();

    $principal = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Principal');
    $relevo    = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Relevo');
    $ayudante  = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Ayudante');

    return view('transaccional.asignacionconductor.edit', compact('viaje', 'conductores', 'principal', 'relevo', 'ayudante'));
}

public function update(Request $request, Viaje $viaje)
{
    $this->authorize('asignacionconductor.editar');

    $request->validate([
        'conductor_principal' => 'required|exists:conductores,id',
        'conductor_relevo'    => 'required|exists:conductores,id|different:conductor_principal',
        'ayudante'             => 'nullable|exists:conductores,id',
    ]);

    AsignacionConductor::updateOrCreate(
        ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Principal'],
        ['conductor_id' => $request->conductor_principal]
    );

    AsignacionConductor::updateOrCreate(
        ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Relevo'],
        ['conductor_id' => $request->conductor_relevo]
    );

    if ($request->filled('ayudante')) {
        AsignacionConductor::updateOrCreate(
            ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Ayudante'],
            ['conductor_id' => $request->ayudante]
        );
    } else {
        AsignacionConductor::where('viaje_id', $viaje->id)
                            ->where('tipo_asignacion', 'Ayudante')
                            ->delete();
    }

    return redirect()->route('transaccional.asignacionconductor.index')
                     ->with('success', 'Asignación actualizada correctamente.');
}

public function destroy(Viaje $viaje)
{
    $this->authorize('asignacionconductor.eliminar');

    AsignacionConductor::where('viaje_id', $viaje->id)->delete();

    return redirect()->route('transaccional.asignacionconductor.index')
                     ->with('success', 'Asignación eliminada correctamente.');
}
}