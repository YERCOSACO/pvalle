<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
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

        return view('cliente.reservas.index', compact('reservas'));
    }

    public function create(): View
    {
        return view('cliente.reservas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:10',
        ]);

        $cliente = auth('cliente')->user();

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => $request->cantidad,
            'total_pagar' => 0,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        return redirect()->route('cliente.boletos.create', $reserva)
                         ->with('success', 'Reserva creada. Ahora selecciona tus asientos y genera el QR de pago.');
    }
}
