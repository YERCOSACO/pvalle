<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('reservas.ver');

        $reservas = Reserva::with('cliente', 'boletos')
                            ->where('estado_base', 1)
                            ->when($request->search, function ($query, $search) {
                                $query->whereHas('cliente', function ($q) use ($search) {
                                    $q->where('nombre', 'like', "%{$search}%")
                                      ->orWhere('apellido', 'like', "%{$search}%");
                                });
                            })
                            ->latest()
                            ->paginate(10)
                            ->withQueryString();

        return view('transaccional.reservas.index', compact('reservas'));
    }

    public function create()
    {
        $this->authorize('reservas.crear');

        $clientes = Cliente::where('estado_base', 1)->get();

        return view('transaccional.reservas.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $this->authorize('reservas.crear');

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cantidad'   => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            Reserva::create([
                'cliente_id'    => $request->cliente_id,
                'fecha_reserva' => now(),
                'cantidad'      => $request->cantidad,
                'estado'        => 'pendiente',
            ]);
        });

        return redirect()->route('transaccional.reservas.index')
                         ->with('success', 'Reserva creada correctamente. Ahora agrega los boletos desde el módulo de Boletos.');
    }

    public function edit(Reserva $reserva)
    {
        $this->authorize('reservas.editar');

        $clientes = Cliente::where('estado_base', 1)->get();

        return view('transaccional.reservas.edit', compact('reserva', 'clientes'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $this->authorize('reservas.editar');

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cantidad'   => 'required|integer|min:1',
            'estado'     => 'required|in:pendiente,confirmada,cancelada',
        ]);

        DB::transaction(function () use ($reserva, $request) {
            $reserva->update($request->only('cliente_id', 'cantidad', 'estado'));
        });

        return redirect()->route('transaccional.reservas.index')
                         ->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(Reserva $reserva)
    {
        $this->authorize('reservas.eliminar');

        DB::transaction(function () use ($reserva) {
            $reserva->update(['estado_base' => 0]);
        });

        return redirect()->route('transaccional.reservas.index')
                         ->with('success', 'Reserva eliminada correctamente.');
    }
}