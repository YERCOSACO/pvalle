<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\AsientoViaje;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsientoViajeController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('asientoviaje.ver');

        $viajes = Viaje::with('ruta', 'bus', 'asignaciones.conductor')
                        ->where('estado_base', 1)
                        ->when($request->search, function ($query, $search) {
                            $query->whereHas('ruta', function ($q) use ($search) {
                                $q->where('origen', 'like', "%{$search}%")
                                  ->orWhere('destino', 'like', "%{$search}%");
                            });
                        })
                        ->paginate(10)
                        ->withQueryString();

        return view('parametrizacion.asientoviaje.index', compact('viajes'));
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

    return view('parametrizacion.asientoviaje.show', compact('viaje', 'asientos'));
}

    public function imprimir(Viaje $asientoviaje)
    {
        $this->authorize('asientoviaje.ver');

        $this->asientosService->todos($asientoviaje->id);
        $viaje = $asientoviaje->load('ruta', 'bus', 'asignaciones.conductor');
        $asientos = AsientoViaje::where('viaje_id', $viaje->id)
            ->where('estado_base', 1)
            ->orderBy('numero_asiento')
            ->get();
        $boletos = $viaje->boletos()
            ->where('estado_base', 1)
            ->whereIn('estado', ['confirmado', 'pendiente'])
            ->where(function ($query) {
                $query->where('estado', 'confirmado')
                      ->orWhere(function ($query) {
                          $query->where('estado', 'pendiente')
                                ->where(function ($query) {
                                    $query->whereNull('expira_en')
                                          ->orWhere('expira_en', '>', now());
                                });
                      });
            })
            ->get()
            ->mapWithKeys(fn ($boleto) => [
                strtoupper(trim($boleto->numero_asiento)) => $boleto,
            ]);

        return view('parametrizacion.asientoviaje.imprimir', compact('viaje', 'asientos', 'boletos'));
    }

    public function edit(AsientoViaje $asientoviaje)
{
    $this->authorize('asientoviaje.editar');
    $asientoviaje->load('viaje.ruta');
    return view('parametrizacion.asientoviaje.edit', compact('asientoviaje'));
}

public function update(Request $request, AsientoViaje $asientoviaje)
{
    $this->authorize('asientoviaje.editar');

    $request->validate([
        'estado' => 'required|in:disponible,ocupado,bloqueado',
    ]);

    DB::transaction(function () use ($asientoviaje, $request) {
        $asientoviaje->update(['estado' => $request->estado]);
    });

    return redirect()->route('parametrizacion.asientoviaje.show', $asientoviaje->viaje_id)
                     ->with('success', 'Estado del asiento actualizado correctamente.');
}

}