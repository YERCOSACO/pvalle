<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $clienteId = auth('cliente')->id();
        $cliente = Cliente::where('estado_base', 1)->findOrFail($clienteId);

        $reservasCount = $cliente->reservas()->where('reservas.estado_base', 1)->count();
        $boletosCount = $cliente->boletos()->where('boletos.estado_base', 1)->count();
        $encomiendasCount = $cliente->encomiendas()->where('encomiendas.estado_base', 1)->count();
        $notificaciones = $cliente->notificaciones()->where('estado_base', 1)
                                    ->latest('fecha_envio')
                                    ->take(5)
                                    ->get();

        return view('cliente.dashboard', compact(
            'cliente',
            'reservasCount',
            'boletosCount',
            'encomiendasCount',
            'notificaciones'
        ));
    }
}
