<?php

namespace App\Http\Controllers\Transaccional;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Viaje;
use App\Models\Bus;
use App\Models\Ruta;
use App\Services\ViajeAsientosService;
use Illuminate\Http\Request;

class ViajeController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function index(Request $request)
{
    $this->authorize('viajes.ver');

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

    return view('transaccional.viajes.index', compact('viajes'));
}

    public function create()
    {
        $this->authorize('viajes.crear');

        $rutas = Ruta::where('estado_base', 1)->get();
        $buses = Bus::where('estado_base', 1)->get();

        return view('transaccional.viajes.create', compact('rutas', 'buses'));
    }

    public function store(Request $request)
{
    $this->authorize('viajes.crear');

    $request->validate([
        'ruta_id'     => 'required|exists:rutas,id',
        'bus_id'      => 'required|exists:buses,id',
        'fecha_viaje' => 'required|date',
        'hora_salida' => 'required',
    ]);

    DB::transaction(function () use ($request) {
        $viaje = Viaje::create($request->only('ruta_id', 'bus_id', 'fecha_viaje', 'hora_salida'));

        $this->asientosService->crearParaViaje($viaje);
    });

    return redirect()->route('transaccional.viajes.index')
                     ->with('success', 'Viaje creado correctamente con sus asientos generados.');
}

    public function edit(Viaje $viaje)
    {
        $this->authorize('viajes.editar');

        $rutas = Ruta::where('estado_base', 1)->get();
        $buses = Bus::where('estado_base', 1)->get();

        return view('transaccional.viajes.edit', compact('viaje', 'rutas', 'buses'));
    }

    public function update(Request $request, Viaje $viaje)
    {
        $this->authorize('viajes.editar');

        $request->validate([
            'ruta_id'     => 'required|exists:rutas,id',
            'bus_id'      => 'required|exists:buses,id',
            'fecha_viaje' => 'required|date',
            'hora_salida' => 'required',
            'estado'      => 'required|in:programado,en curso,finalizado,cancelado',
        ]);

        DB::transaction(function () use ($viaje, $request) {
            $viaje->update($request->only('ruta_id', 'bus_id', 'fecha_viaje', 'hora_salida', 'estado'));
        });

        return redirect()->route('transaccional.viajes.index')
                         ->with('success', 'Viaje actualizado correctamente.');
    }

    public function destroy(Viaje $viaje)
    {
        $this->authorize('viajes.eliminar');

        DB::transaction(function () use ($viaje) {
            $viaje->update(['estado_base' => 0]);
        });

        return redirect()->route('transaccional.viajes.index')
                         ->with('success', 'Viaje eliminado correctamente.');
    }
}