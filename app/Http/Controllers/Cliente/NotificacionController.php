<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        $cliente = auth('cliente')->user();

        $notificaciones = Notificacion::where('cliente_id', $cliente->id)
            ->where('estado_base', 1)
            ->latest('fecha_envio')
            ->get();

        return view('cliente.notificaciones.index', compact('notificaciones'));
    }
}
