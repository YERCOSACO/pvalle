<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function create(): View
    {
        return view('cliente.reservas.create', [
            'viajeId' => request('viaje_id'),
            'cantidad' => request('pasajeros', 1),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            //10//////////////////////////////////////////////////////////////////
            'cantidad' => 'required|integer|min:1|max:4',
            'viaje_id' => 'nullable|integer|exists:viajes,id',
        ]);

        if ($request->filled('viaje_id')) {
            $viaje = Viaje::reservables()->withCount([
                'asientos as asientos_disponibles' => fn ($query) => $query
                    ->activos()
                    ->where('estado', 'disponible'),
            ])->findOrFail($request->viaje_id);

            if ($viaje->asientos_disponibles < $request->cantidad) {
                return back()->withErrors([
                    'cantidad' => 'Ese viaje solo tiene ' . $viaje->asientos_disponibles . ' asiento(s) disponible(s).',
                ])->withInput();
            }
        }

        $cliente = auth('cliente')->user();

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => $request->cantidad,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        return redirect()->route('cliente.boletos.create', [
            'reserva' => $reserva,
            'viaje_id' => $request->viaje_id,
        ])
                         ->with('success', 'Reserva creada. Ahora selecciona tus asientos y genera el QR de pago.');
    }
}
