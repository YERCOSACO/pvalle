<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\AsientoViaje;
use App\Models\Bus;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AsientoViajeController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('asientoviaje.ver');

        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (isset($filtros['desde'], $filtros['hasta']) && $filtros['hasta'] < $filtros['desde']) {
            throw ValidationException::withMessages([
                'hasta' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            ]);
        }

        $viajes = Viaje::with('ruta', 'bus', 'asignaciones.conductor')
                        ->where('estado_base', 1)
                        ->when($filtros['search'] ?? null, function ($query, $search) {
                            $query->whereHas('ruta', function ($q) use ($search) {
                                $q->where('origen', 'like', "%{$search}%")
                                  ->orWhere('destino', 'like', "%{$search}%");
                            });
                        })
                        ->when($filtros['desde'] ?? null, fn ($query, $desde) => $query->whereDate('fecha_viaje', '>=', $desde))
                        ->when($filtros['hasta'] ?? null, fn ($query, $hasta) => $query->whereDate('fecha_viaje', '<=', $hasta))
                        ->orderBy('fecha_viaje')
                        ->orderBy('hora_salida')
                        ->paginate(10)
                        ->withQueryString();

        return view('parametrizacion.asientoviaje.index', compact('viajes', 'filtros'));
    }

    public function gestion(Request $request)
    {
        $this->authorize('asientoviaje.ver');

        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $buses = Bus::query()
            ->where('estado_base', 1)
            ->withCount(['viajes' => fn ($query) => $query->where('estado_base', 1)])
            ->with(['viajes' => fn ($query) => $query
                ->where('estado_base', 1)
                ->with('ruta')
                ->orderByDesc('fecha_viaje')
                ->orderByDesc('hora_salida')
                ->orderByDesc('id')
                ->limit(1)])
            ->when($filtros['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('placa', 'like', "%{$search}%")
                        ->orWhere('modelo', 'like', "%{$search}%")
                        ->orWhere('tipo_bus', 'like', "%{$search}%");
                });
            })
            ->orderBy('placa')
            ->paginate(15)
            ->withQueryString();

        return view('parametrizacion.asientoviaje.gestion', compact('buses', 'filtros'));
    }

    public function viaje(Viaje $viaje)
    {
        $this->authorize('asientoviaje.ver');
        $viaje->load(['bus', 'ruta']);
        abort_unless($viaje->estado_base && $viaje->bus?->estado_base, 404);

        $bus = $viaje->bus;
        $asientosEsperados = $this->asientosService->generarNumeros($bus->capacidad);
        $asientosRegistrados = AsientoViaje::query()
            ->where('viaje_id', $viaje->id)
            ->where('estado_base', 1)
            ->get()
            ->keyBy(fn (AsientoViaje $asiento) => strtoupper(trim($asiento->numero_asiento)));
        $boletos = $this->boletosVigentes($viaje);

        return view('parametrizacion.asientoviaje.viaje', compact(
            'bus',
            'viaje',
            'asientosEsperados',
            'asientosRegistrados',
            'boletos'
        ));
    }

    public function create(Request $request)
    {
        $this->authorize('asientoviaje.crear');

        $filtros = $request->validate([
            'bus_id' => ['nullable', 'integer'],
            'viaje_id' => ['nullable', 'integer'],
        ]);

        $viajeSeleccionado = $filtros['viaje_id'] ?? null;
        $busId = $filtros['bus_id'] ?? null;

        if ($viajeSeleccionado && ! $busId) {
            $viaje = Viaje::query()
                ->where('estado_base', 1)
                ->whereHas('bus', fn ($query) => $query->where('estado_base', 1))
                ->findOrFail($viajeSeleccionado);
            $busId = $viaje->bus_id;
        }

        $buses = Bus::query()
            ->where('estado_base', 1)
            ->orderBy('placa')
            ->get();
        $busSeleccionado = $busId
            ? $buses->firstWhere('id', (int) $busId)
            : null;

        if ($busId && ! $busSeleccionado) {
            abort(404);
        }

        $viajes = $busSeleccionado
            ? $busSeleccionado->viajes()
                ->with('ruta')
                ->where('estado_base', 1)
                ->orderBy('fecha_viaje')
                ->orderBy('hora_salida')
                ->get()
            : collect();

        if ($viajeSeleccionado && ! $viajes->contains('id', (int) $viajeSeleccionado)) {
            abort(404);
        }

        $returnTo = $request->query('return_to') === 'viaje' ? 'viaje' : 'gestion';

        return view('parametrizacion.asientoviaje.create', compact(
            'buses',
            'busSeleccionado',
            'viajes',
            'viajeSeleccionado',
            'returnTo'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('asientoviaje.crear');

        $numeroAsiento = $request->input('numero_asiento');
        if (is_string($numeroAsiento)) {
            $request->merge(['numero_asiento' => strtoupper(trim($numeroAsiento))]);
        }

        $datos = $request->validate([
            'viaje_id' => ['required', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
            'numero_asiento' => ['required', 'string', 'max:10', 'regex:/^\d+[A-D]$/'],
        ]);

        $viaje = Viaje::with('bus')
            ->where('estado_base', 1)
            ->whereHas('bus', fn ($query) => $query->where('estado_base', 1))
            ->findOrFail($datos['viaje_id']);

        if (! in_array($datos['numero_asiento'], $this->asientosService->generarNumeros($viaje->bus->capacidad), true)) {
            throw ValidationException::withMessages([
                'numero_asiento' => 'El número de asiento no corresponde a la capacidad del bus seleccionado.',
            ]);
        }

        DB::transaction(function () use ($viaje, $datos) {
            $asiento = AsientoViaje::query()
                ->where('viaje_id', $viaje->id)
                ->where('numero_asiento', $datos['numero_asiento'])
                ->lockForUpdate()
                ->first();

            if ($asiento && $asiento->estado_base) {
                throw ValidationException::withMessages([
                    'numero_asiento' => 'Ese asiento ya está registrado para el viaje seleccionado.',
                ]);
            }

            if ($asiento && $this->asientoTieneBoletoVigente($asiento)) {
                throw ValidationException::withMessages([
                    'numero_asiento' => 'No se puede reactivar un asiento asociado a un boleto vigente.',
                ]);
            }

            if ($asiento) {
                $asiento->update(['estado_base' => 1, 'estado' => 'disponible']);
                return;
            }

            AsientoViaje::create([
                'viaje_id' => $viaje->id,
                'numero_asiento' => $datos['numero_asiento'],
                'estado' => 'disponible',
                'estado_base' => 1,
            ]);
        });

        if ($request->input('return_to') === 'viaje') {
            return redirect()->route('parametrizacion.asientoviaje.viaje', $viaje)
                ->with('success', "Asiento {$datos['numero_asiento']} agregado correctamente.");
        }

        return redirect()->route('parametrizacion.asientoviaje.gestion')
            ->with('success', "Asiento {$datos['numero_asiento']} agregado correctamente.");
    }

    public function show(Viaje $asientoviaje)
    {
        $this->authorize('asientoviaje.ver');

        $viaje = $asientoviaje->load('ruta', 'bus');
        $this->asientosService->todos($viaje->id);

        $asientos = AsientoViaje::where('viaje_id', $viaje->id)
            ->where('estado_base', 1)
            ->orderBy('numero_asiento')
            ->get();
        $todos = $asientos->pluck('numero_asiento')->all();
        $boletos = $this->boletosVigentes($viaje);
        $totalAsientos = $asientos->count();
        $totalDisponibles = $asientos->where('estado', 'disponible')->count();
        $totalOcupados = $asientos->where('estado', 'ocupado')->count();
        $totalBloqueados = $asientos->where('estado', 'bloqueado')->count();
        $porcentaje = $totalAsientos > 0 ? round($totalOcupados * 100 / $totalAsientos) : 0;

        return view('parametrizacion.asientoviaje.show', compact(
            'viaje',
            'asientos',
            'todos',
            'boletos',
            'totalAsientos',
            'totalDisponibles',
            'totalOcupados',
            'totalBloqueados',
            'porcentaje'
        ));
    }

    public function imprimir(Request $request, Viaje $asientoviaje)
    {
        $this->authorize('asientoviaje.ver');

        $this->asientosService->todos($asientoviaje->id);
        $viaje = $asientoviaje->load('ruta', 'bus', 'asignaciones.conductor');
        $asientos = AsientoViaje::where('viaje_id', $viaje->id)
            ->where('estado_base', 1)
            ->orderBy('numero_asiento')
            ->get();
        $boletos = $this->boletosVigentes($viaje);

        $autoPrint = $request->boolean('auto_print');

        return view('parametrizacion.asientoviaje.imprimir', compact('viaje', 'asientos', 'boletos', 'autoPrint'));
    }

    private function boletosVigentes(Viaje $viaje)
    {
        return $viaje->boletos()
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
    }

    public function edit(Request $request, AsientoViaje $asientoviaje)
    {
        $this->authorize('asientoviaje.editar');
        abort_unless($asientoviaje->estado_base, 404);

        $asientoviaje->load('viaje.ruta');
        $volverGestion = in_array($request->query('return_to'), ['gestion', 'viaje'], true);
        $returnTo = $request->query('return_to');

        return view('parametrizacion.asientoviaje.edit', compact('asientoviaje', 'volverGestion', 'returnTo'));
    }

    public function update(Request $request, AsientoViaje $asientoviaje)
    {
        $this->authorize('asientoviaje.editar');
        abort_unless($asientoviaje->estado_base, 404);

        $datos = $request->validate([
            'estado' => ['required', 'in:disponible,ocupado,bloqueado'],
        ]);
        $returnTo = $request->input('return_to');

        DB::transaction(function () use ($asientoviaje, $datos) {
            $asiento = AsientoViaje::query()->lockForUpdate()->findOrFail($asientoviaje->id);

            if ($this->asientoTieneBoletoVigente($asiento) && $datos['estado'] !== 'ocupado') {
                throw ValidationException::withMessages([
                    'estado' => 'No se puede liberar ni bloquear un asiento asociado a un boleto vigente.',
                ]);
            }

            $asiento->update(['estado' => $datos['estado']]);
        });

        if ($returnTo === 'viaje') {
            return redirect()->route('parametrizacion.asientoviaje.viaje', $asientoviaje->viaje)
                ->with('success', 'Estado del asiento actualizado correctamente.');
        }

        if ($returnTo === 'gestion') {
            return redirect()->route('parametrizacion.asientoviaje.gestion')
                ->with('success', 'Estado del asiento actualizado correctamente.');
        }

        return redirect()->route('parametrizacion.asientoviaje.show', $asientoviaje->viaje_id)
            ->with('success', 'Estado del asiento actualizado correctamente.');
    }

    public function destroy(Request $request, AsientoViaje $asientoviaje)
    {
        $this->authorize('asientoviaje.eliminar');

        DB::transaction(function () use ($asientoviaje) {
            $asiento = AsientoViaje::query()->lockForUpdate()->findOrFail($asientoviaje->id);

            if (! $asiento->estado_base) {
                throw ValidationException::withMessages([
                    'asiento' => 'El asiento ya está eliminado.',
                ]);
            }

            if ($this->asientoTieneBoletoVigente($asiento)) {
                throw ValidationException::withMessages([
                    'asiento' => 'No se puede eliminar un asiento asociado a un boleto vigente.',
                ]);
            }

            $asiento->update(['estado_base' => 0]);
        });

        if ($request->input('return_to') === 'viaje') {
            return redirect()->route('parametrizacion.asientoviaje.viaje', $asientoviaje->viaje)
                ->with('success', 'Asiento eliminado correctamente.');
        }

        return redirect()->route('parametrizacion.asientoviaje.gestion')
            ->with('success', 'Asiento eliminado correctamente.');
    }

    private function asientoTieneBoletoVigente(AsientoViaje $asiento): bool
    {
        return $this->boletosVigentes($asiento->viaje)
            ->has(strtoupper(trim($asiento->numero_asiento)));
    }

}