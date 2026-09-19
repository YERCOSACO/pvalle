<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Reserva;
use App\Models\Viaje;
use App\Models\Bus;
use App\Models\AsientoViaje;
use App\Services\ViajeAsientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BoletoController extends Controller
{
    // ── Recargos por tipo de pasajero ──────────────────────────
    private const RECARGO_COMODIDAD = 10;
    private const RECARGO_MASCOTA   = 15;

    // ── Calcula asientos ocupados en un viaje ──────────────────
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('boletos.ver');

        // Busca el viaje más próximo/reciente activo
        $viaje = Viaje::conConductorAsignado()
                  ->where('estado_base', 1)
                      ->where('fecha_viaje', '>=', now()->toDateString())
                      ->orderBy('fecha_viaje', 'asc')
                      ->first();

        // Si no hay viajes futuros, obtiene el último registrado
        if (!$viaje) {
            $viaje = Viaje::conConductorAsignado()
                          ->where('estado_base', 1)
                          ->latest()
                          ->first();
        }

        // Si existe un viaje, entra directo a la vista con el bus y asientos
        if ($viaje) {
            return redirect()->route('transaccional.boletos.por-viaje', $viaje);
        }

        // En caso extremo de que no haya ningún viaje cargado en BD
        return view('transaccional.boletos.index', ['viajes' => collect()]);
    }

    public function create()
    {
        $this->authorize('boletos.crear');

        $reservas = Reserva::with('cliente')
                            ->where('estado_base', 1)
                            ->where('estado', 'pendiente')
                                                        ->whereDoesntHave('boletos', function ($query) {
                                                                $query->where('estado_base', 1)
                                                                            ->where('estado', '!=', 'cancelado');
                                                        })
                            ->get();

        $viajes = Viaje::with('ruta', 'bus')
            ->reservables()
            ->conConductorAsignado()
                ->orderBy('fecha_viaje')
                ->orderBy('hora_salida')
                ->get()
                ->filter(fn ($viaje) => count($this->asientosService->ocupados($viaje->id)) < $viaje->bus->capacidad)
                ->values();

        return view('transaccional.boletos.create', compact('reservas', 'viajes'));
    }

    // ── AJAX: asientos disponibles para un viaje ───────────────
    public function asientosDisponibles(Viaje $viaje)
{
    $viaje->load('bus', 'ruta');

    $todos = $this->asientosService->todos($viaje->id);
    $ocupados = $this->asientosService->ocupados($viaje->id);
    $disponibles = $this->asientosService->disponibles($viaje->id);

    return response()->json([
        'disponibles' => $disponibles,
        'ocupados'    => $ocupados,
        'todos'       => $todos,
        'precio'      => $viaje->ruta->precio_base,
    ]);
}

    public function store(Request $request)
{
    $this->authorize('boletos.crear');

    $request->validate([
        'reserva_id'          => 'required|exists:reservas,id',
        'viaje_id'            => 'required|exists:viajes,id',
        'numero_asiento'      => 'required|array|min:1',
        'numero_asiento.*'    => 'required|string',
        'tipo_pasajero'       => 'required|array',
        'tipo_pasajero.*'     => 'required|in:normal,comodidad,mascota',
        'nombre_pasajero'     => 'nullable|array',
        'nombre_pasajero.*'   => 'nullable|string|max:255',
        'ci_pasajero'         => 'nullable|array',
        'ci_pasajero.*'       => 'nullable|string|max:20',
        'telefono_pasajero'   => 'nullable|array',
        'telefono_pasajero.*' => 'nullable|string|max:20',
        'precio'              => 'required|numeric|min:0',
        'metodo_pago'         => 'required|in:QR,efectivo,tarjeta',
    ]);

DB::transaction(function () use ($request) {
    $viaje = Viaje::with('ruta')->findOrFail($request->viaje_id);
    $reserva = Reserva::with('cliente')->findOrFail($request->reserva_id);
    $tieneBoletos = $reserva->boletos()
        ->where('estado_base', 1)
        ->where('estado', '!=', 'cancelado')
        ->exists();

    if ($tieneBoletos) {
        throw ValidationException::withMessages([
            'reserva_id' => 'Esta reserva ya tiene boletos registrados y no puede reutilizarse.',
        ]);
    }

    $this->asientosService->reservar($request->viaje_id, $request->numero_asiento);
    $estado = $request->metodo_pago === 'QR' ? 'pendiente' : 'confirmado';
    $expiraEn = $request->metodo_pago === 'QR' ? Carbon::now()->addMinutes(15) : null;
    $primerBoleto = null;
    $asientosSeleccionados = [];

    foreach ($request->numero_asiento as $i => $asiento) {
        $tipo = $request->tipo_pasajero[$i];

        $precioFinal = $viaje->ruta->precio_base;
        if ($tipo === 'comodidad') {
            $precioFinal += self::RECARGO_COMODIDAD;
        } elseif ($tipo === 'mascota') {
            $precioFinal += self::RECARGO_MASCOTA;
        }

        $nombre = match ($tipo) {
            'comodidad' => 'Comodidad (espacio extra)',
            'mascota'   => 'Mascota',
            default     => $request->nombre_pasajero[$i],
        };

        $boleto = Boleto::updateOrCreate(
            [
                'viaje_id'       => $request->viaje_id,
                'numero_asiento' => $asiento,
            ],
            [
                'reserva_id'        => $request->reserva_id,
                'nombre_pasajero'   => $nombre,
                'ci_pasajero'       => $request->ci_pasajero[$i] ?? null,
                'telefono_pasajero' => $request->telefono_pasajero[$i] ?? null,
                'espacio_extra'     => $tipo === 'comodidad',
                'mascota'           => $tipo === 'mascota',
                'precio'            => $precioFinal,
                'metodo_pago'       => $request->metodo_pago,
                'estado'            => $estado,
                'expira_en'         => $expiraEn,
                'estado_base'       => 1,
            ]
        );

        $primerBoleto = $primerBoleto ?? $boleto;
        $asientosSeleccionados[] = $asiento;
        $ocupados[] = $asiento;
    }

    $tieneConfirmados = $reserva->boletos()
        ->where('estado_base', 1)
        ->where('estado', 'confirmado')
        ->exists();
    $reserva->update([
        'estado' => $tieneConfirmados ? 'confirmada' : 'pendiente',
    ]);

    if ($reserva->cliente && $primerBoleto) {
        $asientosTexto = implode(', ', $asientosSeleccionados);
        $titulo = $request->metodo_pago === 'QR'
            ? 'Pago pendiente de boleto'
            : 'Tu boleto ha sido confirmado';
        $fechaViaje = Carbon::parse($viaje->fecha_viaje)->format('d/m/Y');
        $mensaje = $request->metodo_pago === 'QR'
            ? "Tu/ Tus boleto(s) para {$viaje->ruta->nombre_ruta} el {$fechaViaje} a las {$viaje->hora_salida} están pendientes de pago. Asiento(s): {$asientosTexto}."
            : "Tu/ Tus boleto(s) para {$viaje->ruta->nombre_ruta} el {$fechaViaje} a las {$viaje->hora_salida} han sido confirmados. Asiento(s): {$asientosTexto}.";

        NotificacionController::crearAutomatica(
            $reserva->cliente->id,
            $titulo,
            $mensaje,
            $primerBoleto,
            'info',
            'normal'
        );
    }
});
    $mensaje = $request->metodo_pago === 'QR'
        ? 'Boleto(s) creado(s). Tienes 15 minutos para completar el pago por QR.'
        : 'Boleto(s) registrado(s) y confirmado(s) correctamente.';

    return redirect()->route('transaccional.boletos.index')->with('success', $mensaje);
}
    // ── Confirmar pago QR manualmente ──────────────────────────
    public function confirmarPago(Boleto $boleto)
    {
        $this->authorize('boletos.editar');
        if ($boleto->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'Solo se pueden confirmar boletos pendientes.']);
        }
        if ($boleto->esta_expirado) {
            return back()->withErrors(['error' => 'Este boleto expiró. Ya no se puede confirmar.']);
        }
        DB::transaction(function () use ($boleto) {
            $boleto->update([
                'estado'    => 'confirmado',
                'expira_en' => null,
            ]);
            $boleto->load('reserva.cliente', 'viaje.ruta');
            if ($boleto->reserva) {
                $tieneConfirmados = $boleto->reserva->boletos()
                    ->where('estado_base', 1)
                    ->where('estado', 'confirmado')
                    ->exists();
                $boleto->reserva->update([
                    'estado' => $tieneConfirmados ? 'confirmada' : 'pendiente',
                ]);
            }
            if ($boleto->reserva?->cliente) {
                $fechaViaje = Carbon::parse($boleto->viaje->fecha_viaje)->format('d/m/Y');
                NotificacionController::crearAutomatica(
                    $boleto->reserva->cliente->id,
                    'Pago del boleto confirmado',
                    "Tu boleto para {$boleto->viaje->ruta->nombre_ruta} el {$fechaViaje} a las {$boleto->viaje->hora_salida} y asiento {$boleto->numero_asiento} fue confirmado.",
                    $boleto,
                    'info',
                    'normal'
                );
            }
        });
        return back()->with('success', 'Pago confirmado correctamente.');
    }
    public function edit(Boleto $boleto)
    {
        $this->authorize('boletos.editar');

        $boleto->load('viaje.ruta');

        return view('transaccional.boletos.edit', compact('boleto'));
    }
    public function update(Request $request, Boleto $boleto)
    {
        $this->authorize('boletos.editar');

        $request->validate([
            'nombre_pasajero'   => 'nullable|string|max:255',
            'ci_pasajero'       => 'nullable|string|max:20',
            'telefono_pasajero' => 'nullable|string|max:20',
            'tipo_pasajero'     => 'required|in:normal,comodidad,mascota',
            'estado'            => 'required|in:pendiente,confirmado,cancelado',
        ]);
        $estadoAnterior = $boleto->estado;
        $nombre = match ($request->tipo_pasajero) {
            'comodidad' => 'Comodidad (espacio extra)',
            'mascota'   => 'Mascota',
            default     => $request->nombre_pasajero,
        };
        DB::transaction(function () use ($boleto, $request, $nombre, $estadoAnterior) {
            $boleto->update([
                'nombre_pasajero'   => $nombre,
                'ci_pasajero'       => $request->ci_pasajero,
                'telefono_pasajero' => $request->telefono_pasajero,
                'espacio_extra'     => $request->tipo_pasajero === 'comodidad',
                'mascota'           => $request->tipo_pasajero === 'mascota',
                'estado'            => $request->estado,
            ]);

            if ($estadoAnterior !== 'cancelado' && $boleto->estado === 'cancelado') {
                $this->asientosService->liberar($boleto->viaje_id, [$boleto->numero_asiento]);
            }

            if ($estadoAnterior !== $boleto->estado) {
                $boleto->load('reserva.cliente', 'viaje.ruta');
                if ($boleto->reserva) {
                    $tieneConfirmados = $boleto->reserva->boletos()
                        ->where('estado_base', 1)
                        ->where('estado', 'confirmado')
                        ->exists();

                    $boleto->reserva->update([
                        'estado' => $tieneConfirmados ? 'confirmada' : 'pendiente',
                    ]);
                }

                if ($boleto->reserva?->cliente) {
                    $fechaViaje = Carbon::parse($boleto->viaje->fecha_viaje)->format('d/m/Y');
                    NotificacionController::crearAutomatica(
                        $boleto->reserva->cliente->id,
                        'Cambio de estado del boleto',
                        "El estado de tu boleto para {$boleto->viaje->ruta->nombre_ruta} el {$fechaViaje} a las {$boleto->viaje->hora_salida} y asiento {$boleto->numero_asiento} cambió a {$boleto->estado}.",
                        $boleto,
                        'info',
                        'normal'
                    );
                }
            }
        });

        return redirect()->route('transaccional.boletos.index')
                         ->with('success', 'Boleto actualizado correctamente.');
    }

    public function destroy(Boleto $boleto)
    {
        $this->authorize('boletos.eliminar');

        DB::transaction(function () use ($boleto) {
            $boleto->update(['estado_base' => 0]);
        });

        return redirect()->route('transaccional.boletos.index')
                         ->with('success', 'Boleto eliminado correctamente.');
    }
    public function porViaje(Viaje $viaje)
{
    $this->authorize('boletos.ver');

    $viaje->load('ruta', 'bus');
    $viajesDisponibles = Viaje::with('ruta', 'bus')
        ->reservables()
        ->conConductorAsignado()
        ->orderBy('fecha_viaje')
        ->orderBy('hora_salida')
        ->get();

        $asientos = AsientoViaje::query()
            ->where('viaje_id', $viaje->id)
            ->where('estado_base', 1)
            ->orderBy('numero_asiento')
            ->get(['numero_asiento', 'estado']);
        $todos = $asientos->pluck('numero_asiento')->all();
        $estadosAsientos = $asientos->mapWithKeys(
            fn (AsientoViaje $asiento) => [strtoupper(trim($asiento->numero_asiento)) => $asiento->estado]
        );

    // Mostrar boletos activos del viaje para distinguir confirmados y pendientes.
    $boletos = Boleto::where('viaje_id', $viaje->id)
                      ->where('estado_base', 1)
                      ->whereIn('estado', ['confirmado', 'pendiente'])
                      ->get()
                      ->mapWithKeys(fn (Boleto $boleto) => [
                          strtoupper(trim($boleto->numero_asiento)) => $boleto,
                      ]);

    $totalAsientos    = count($todos);
        $totalOcupados    = count($this->asientosService->ocupados($viaje->id));
    $totalDisponibles = $totalAsientos - $totalOcupados;
    $porcentaje       = $totalAsientos > 0 ? round(($totalOcupados / $totalAsientos) * 100) : 0;

    return view('transaccional.boletos.show', compact(
        'viaje', 'todos', 'boletos', 'estadosAsientos',
        'totalAsientos', 'totalOcupados', 'totalDisponibles', 'porcentaje',
        'viajesDisponibles'
    ));
}
public function imprimir(Boleto $boleto)
{
    $boleto->load([
        'viaje.ruta',
        'viaje.bus',
        'reserva'
    ]);

    return view('transaccional.boletos.imprimir', compact('boleto'));
}
}