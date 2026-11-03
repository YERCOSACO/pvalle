<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\AsignacionConductor;
use App\Models\Viaje;
use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsignacionConductorController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('asignacionconductor.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

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

    $datos = $request->validate([
        'viaje_id'             => ['required', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
        'conductor_principal'  => ['required', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1)],
        'conductor_relevo'     => ['nullable', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1), 'different:conductor_principal'],
        'tipo_ayudante'        => 'required|in:ninguno,conductor,nombre',
        'ayudante'             => ['required_if:tipo_ayudante,conductor', 'nullable', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1), 'different:conductor_principal,conductor_relevo'],
        'nombre_ayudante'      => 'required_if:tipo_ayudante,nombre|nullable|string|max:255',
    ]);

    $yaExiste = AsignacionConductor::where('viaje_id', $datos['viaje_id'])
                                    ->where('tipo_asignacion', 'Principal')
                                    ->exists();

    if ($yaExiste) {
        return back()->withErrors(['viaje_id' => 'Este viaje ya tiene conductores asignados.']);
    }

    DB::transaction(function () use ($datos) {
        AsignacionConductor::create([
            'viaje_id'        => $datos['viaje_id'],
            'conductor_id'    => $datos['conductor_principal'],
            'tipo_asignacion' => 'Principal',
        ]);

        if (! empty($datos['conductor_relevo'])) {
            AsignacionConductor::create([
                'viaje_id'        => $datos['viaje_id'],
                'conductor_id'    => $datos['conductor_relevo'],
                'tipo_asignacion' => 'Relevo',
            ]);
        }

        if ($datos['tipo_ayudante'] === 'conductor') {
            AsignacionConductor::create([
                'viaje_id'        => $datos['viaje_id'],
                'conductor_id'    => $datos['ayudante'],
                'tipo_asignacion' => 'Ayudante',
            ]);
        } elseif ($datos['tipo_ayudante'] === 'nombre') {
            AsignacionConductor::create([
                'viaje_id'        => $datos['viaje_id'],
                'tipo_asignacion' => 'Ayudante',
                'nombre_ayudante' => $datos['nombre_ayudante'],
            ]);
        }
    });

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

    $datos = $request->validate([
        'conductor_principal' => ['required', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1)],
        'conductor_relevo'    => ['nullable', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1), 'different:conductor_principal'],
        'tipo_ayudante'       => 'required|in:ninguno,conductor,nombre',
        'ayudante'            => ['required_if:tipo_ayudante,conductor', 'nullable', 'integer', Rule::exists('conductores', 'id')->where('estado_base', 1), 'different:conductor_principal,conductor_relevo'],
        'nombre_ayudante'     => 'required_if:tipo_ayudante,nombre|nullable|string|max:255',
    ]);

    DB::transaction(function () use ($datos, $viaje) {
        AsignacionConductor::updateOrCreate(
            ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Principal'],
            ['conductor_id' => $datos['conductor_principal']]
        );

        if (! empty($datos['conductor_relevo'])) {
            AsignacionConductor::updateOrCreate(
                ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Relevo'],
                ['conductor_id' => $datos['conductor_relevo'], 'nombre_ayudante' => null]
            );
        } else {
            AsignacionConductor::where('viaje_id', $viaje->id)
                                ->where('tipo_asignacion', 'Relevo')
                                ->delete();
        }

        if ($datos['tipo_ayudante'] === 'conductor') {
            AsignacionConductor::updateOrCreate(
                ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Ayudante'],
                ['conductor_id' => $datos['ayudante'], 'nombre_ayudante' => null]
            );
        } elseif ($datos['tipo_ayudante'] === 'nombre') {
            AsignacionConductor::updateOrCreate(
                ['viaje_id' => $viaje->id, 'tipo_asignacion' => 'Ayudante'],
                ['conductor_id' => null, 'nombre_ayudante' => $datos['nombre_ayudante']]
            );
        } else {
            AsignacionConductor::where('viaje_id', $viaje->id)
                                ->where('tipo_asignacion', 'Ayudante')
                                ->delete();
        }
    });

    return redirect()->route('transaccional.asignacionconductor.index')
                     ->with('success', 'Asignación actualizada correctamente.');
}

public function destroy(Viaje $viaje)
{
    $this->authorize('asignacionconductor.eliminar');

    DB::transaction(function () use ($viaje) {
        AsignacionConductor::where('viaje_id', $viaje->id)->delete();
    });

    return redirect()->route('transaccional.asignacionconductor.index')
                     ->with('success', 'Asignación eliminada correctamente.');
}
}