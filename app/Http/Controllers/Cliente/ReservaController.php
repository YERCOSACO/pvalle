<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ReservaController extends Controller
{
    public function index(Request $request): View
    {
        $cliente = auth('cliente')->user();

        $reservas = Reserva::with('boletos')
            ->where('cliente_id', $cliente->id)
            ->where('estado_base', 1)
            ->latest()
            ->get();

        $reservasPorViaje = $reservas
            ->filter(fn ($reserva) => $reserva->boletos->isNotEmpty())
            ->groupBy(fn ($reserva) => $reserva->boletos->first()->viaje_id);
        $reservasSinViaje = $reservas->filter(fn ($reserva) => $reserva->boletos->isEmpty());

        return view('cliente.reservas.index', compact('reservasPorViaje', 'reservasSinViaje'));
    }

    public function create(Request $request): View
    {
        $data = $request->validate([
            'viaje_id' => ['sometimes', 'nullable', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
            'pasajeros' => 'sometimes|integer|min:1|max:4',
        ]);

        return view('cliente.reservas.create', [
            'viajeId' => $data['viaje_id'] ?? null,
            'cantidad' => $data['pasajeros'] ?? 1,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cantidad' => 'required|integer|min:1|max:4',
            'viaje_id' => ['nullable', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
        ]);

        if (! empty($datos['viaje_id'])) {
            $viaje = Viaje::reservables()->withCount([
                'asientos as asientos_disponibles' => fn ($query) => $query
                    ->activos()
                    ->where('estado', 'disponible'),
            ])->findOrFail($datos['viaje_id']);

            if ($viaje->asientos_disponibles < $datos['cantidad']) {
                return back()->withErrors([
                    'cantidad' => 'Ese viaje solo tiene ' . $viaje->asientos_disponibles . ' asiento(s) disponible(s).',
                ])->withInput();
            }
        }

        $cliente = auth('cliente')->user();

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => $datos['cantidad'],
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        return redirect()->route('cliente.boletos.create', [
            'reserva' => $reserva,
            'viaje_id' => $datos['viaje_id'] ?? null,
        ])
                         ->with('success', 'Solicitud iniciada. Aún debes elegir los asientos y completar el proceso de pago; esto no confirma tu boleto.');
    }
}
