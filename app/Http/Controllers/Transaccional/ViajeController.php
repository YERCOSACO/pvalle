<?php

namespace App\Http\Controllers\Transaccional;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Viaje;
use App\Models\Bus;
use App\Models\Ruta;
use App\Services\ViajeAsientosService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViajeController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function index(Request $request)
{
    $this->authorize('viajes.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

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

    $datos = $request->validate([
        'ruta_id'     => ['required', 'integer', Rule::exists('rutas', 'id')->where('estado_base', 1)],
        'bus_id'      => ['required', 'integer', Rule::exists('buses', 'id')->where('estado_base', 1)],
        'fecha_viaje' => 'required|date_format:Y-m-d',
        'hora_salida' => 'required|date_format:H:i,H:i:s',
    ]);

    DB::transaction(function () use ($datos) {
        $viaje = Viaje::create($datos);

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

        $datos = $request->validate([
            'ruta_id'     => ['required', 'integer', Rule::exists('rutas', 'id')->where('estado_base', 1)],
            'bus_id'      => ['required', 'integer', Rule::exists('buses', 'id')->where('estado_base', 1)],
            'fecha_viaje' => 'required|date_format:Y-m-d',
            'hora_salida' => 'required|date_format:H:i,H:i:s',
            'estado'      => 'required|in:programado,en curso,retrasado,finalizado,cancelado',
        ]);

        DB::transaction(function () use ($viaje, $datos) {
            $viaje->update($datos);
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