<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Reserva;
use App\Models\Viaje;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoletoController extends Controller
{
    // ── Recargos por tipo de pasajero ──────────────────────────
    private const RECARGO_COMODIDAD = 10;
    private const RECARGO_MASCOTA   = 15;

    // ── Calcula asientos ocupados en un viaje ──────────────────
    private function asientosOcupados(int $viajeId): array
    {
        return Boleto::where('viaje_id', $viajeId)
                      ->where('estado_base', 1)
                      ->where(function ($query) {
                          $query->where('estado', 'confirmado')
                                ->orWhere(function ($q) {
                                    $q->where('estado', 'pendiente')
                                      ->where('expira_en', '>', now());
                                });
                      })
                      ->pluck('numero_asiento')
                      ->toArray();
    }

    // ── Genera todos los asientos del bus según capacidad ──────
    private function generarAsientos(int $capacidad): array
    {
        $asientos = [];
        $letras = ['A', 'B', 'C', 'D'];
        $filas = ceil($capacidad / 4);
        $count = 0;

        foreach (range(1, $filas) as $fila) {
            foreach ($letras as $letra) {
                if ($count >= $capacidad) break;
                $asientos[] = $fila . $letra;
                $count++;
            }
        }

        return $asientos;
    }

    public function index(Request $request)
{
    $this->authorize('boletos.ver');

    $viajes = Viaje::with('ruta', 'bus')
                    ->where('estado_base', 1)
                    ->when($request->search, function ($query, $search) {
                        $query->whereHas('ruta', function ($q) use ($search) {
                            $q->where('origen', 'like', "%{$search}%")
                              ->orWhere('destino', 'like', "%{$search}%");
                        });
                    })
                    ->withCount([
                        'boletos as confirmados' => fn($q) => $q->where('estado', 'confirmado')->where('estado_base', 1),
                        'boletos as pendientes'  => fn($q) => $q->where('estado', 'pendiente')->where('expira_en', '>', now())->where('estado_base', 1),
                    ])
                    ->latest()
                    ->paginate(10)
                    ->withQueryString();

    return view('transaccional.boletos.index', compact('viajes'));
}

    public function create()
    {
        $this->authorize('boletos.crear');

        $reservas = Reserva::with('cliente')
                            ->where('estado_base', 1)
                            ->where('estado', 'pendiente')
                            ->get();

        $viajes = Viaje::with('ruta', 'bus')
                        ->where('estado_base', 1)
                        ->get();

        return view('transaccional.boletos.create', compact('reservas', 'viajes'));
    }

    // ── AJAX: asientos disponibles para un viaje ───────────────
    public function asientosDisponibles(Viaje $viaje)
{
    $viaje->load('bus', 'ruta');

    $todos = $this->generarAsientos($viaje->bus->capacidad);
    $ocupados = $this->asientosOcupados($viaje->id);

    $disponibles = array_values(array_diff($todos, $ocupados));

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
    $reserva = Reserva::findOrFail($request->reserva_id);
    $ocupados = $this->asientosOcupados($request->viaje_id);
    $estado = $request->metodo_pago === 'QR' ? 'pendiente' : 'confirmado';
    $expiraEn = $request->metodo_pago === 'QR' ? Carbon::now()->addMinutes(15) : null;

    foreach ($request->numero_asiento as $i => $asiento) {
        if (in_array($asiento, $ocupados)) {
            throw new \Exception("El asiento {$asiento} ya fue tomado. Por favor elige otro.");
        }

        $tipo = $request->tipo_pasajero[$i];

        $precioFinal = $viaje->ruta->precio_base;
        if ($tipo === 'comodidad') {
            $precioFinal += self::RECARGO_COMODIDAD;
        } elseif ($tipo === 'mascota') {
            $precioFinal += self::RECARGO_MASCOTA;
        }

        // nombre_pasajero es NOT NULL en la BD: si no es pasajero normal,
        // le ponemos una etiqueta fija en vez de null
        $nombre = match ($tipo) {
            'comodidad' => 'Comodidad (espacio extra)',
            'mascota'   => 'Mascota',
            default     => $request->nombre_pasajero[$i],
        };

        // updateOrCreate en vez de create: si ya existe una fila vieja
        // (expirada/cancelada) para este viaje+asiento, la reescribe en
        // vez de chocar contra la restricción única (viaje_id, numero_asiento)
        Boleto::updateOrCreate(
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

        $ocupados[] = $asiento; // evita que el mismo asiento se seleccione 2 veces en el mismo envío
    }

    $reserva->update([
        'total_pagar' => $reserva->boletos()->where('estado', '!=', 'cancelado')->sum('precio'),
    ]);

    $confirmados = $reserva->boletos()->where('estado', 'confirmado')->count();
    if ($confirmados >= $reserva->cantidad) {
        $reserva->update(['estado' => 'confirmada']);
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

        if ($boleto->esta_expirado) {
            return back()->withErrors(['error' => 'Este boleto expiró. Ya no se puede confirmar.']);
        }

        $boleto->update([
            'estado'    => 'confirmado',
            'expira_en' => null,
        ]);

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

        // nombre_pasajero es NOT NULL en la BD: si no es pasajero normal,
        // le ponemos una etiqueta fija en vez de null
        $nombre = match ($request->tipo_pasajero) {
            'comodidad' => 'Comodidad (espacio extra)',
            'mascota'   => 'Mascota',
            default     => $request->nombre_pasajero,
        };

        $boleto->update([
            'nombre_pasajero'   => $nombre,
            'ci_pasajero'       => $request->ci_pasajero,
            'telefono_pasajero' => $request->telefono_pasajero,
            'espacio_extra'     => $request->tipo_pasajero === 'comodidad',
            'mascota'           => $request->tipo_pasajero === 'mascota',
            'estado'            => $request->estado,
        ]);

        return redirect()->route('transaccional.boletos.index')
                         ->with('success', 'Boleto actualizado correctamente.');
    }

    public function destroy(Boleto $boleto)
    {
        $this->authorize('boletos.eliminar');

        $boleto->update(['estado_base' => 0]);

        return redirect()->route('transaccional.boletos.index')
                         ->with('success', 'Boleto eliminado correctamente.');
    }
    public function porViaje(Viaje $viaje)
{
    $this->authorize('boletos.ver');

    $viaje->load('ruta', 'bus');

    // Genera todos los asientos
    $letras = ['A', 'B', 'C', 'D'];
    $filas = ceil($viaje->bus->capacidad / 4);
    $todos = [];
    $count = 0;
    foreach (range(1, $filas) as $fila) {
        foreach ($letras as $letra) {
            if ($count >= $viaje->bus->capacidad) break;
            $todos[] = $fila . $letra;
            $count++;
        }
    }

    // Boletos activos — confirmados + pendientes no expirados
    $boletos = Boleto::where('viaje_id', $viaje->id)
                      ->where('estado_base', 1)
                      ->where(function ($q) {
                          $q->where('estado', 'confirmado')
                            ->orWhere(function ($q2) {
                                $q2->where('estado', 'pendiente')
                                   ->where('expira_en', '>', now());
                            });
                      })
                      ->get()
                      ->keyBy('numero_asiento');

    $totalAsientos    = count($todos);
    $totalOcupados    = $boletos->count();
    $totalDisponibles = $totalAsientos - $totalOcupados;
    $porcentaje       = $totalAsientos > 0 ? round(($totalOcupados / $totalAsientos) * 100) : 0;

    return view('transaccional.boletos.show', compact(
        'viaje', 'todos', 'boletos',
        'totalAsientos', 'totalOcupados', 'totalDisponibles', 'porcentaje'
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