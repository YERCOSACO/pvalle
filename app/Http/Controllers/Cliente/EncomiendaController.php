<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Encomienda;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EncomiendaController extends Controller
{
    public function index(Request $request): View
    {
        $cliente = auth('cliente')->user();

        $encomiendas = Encomienda::with('viaje.ruta', 'tipoEncomienda')
            ->where('cliente_id', $cliente->id)
            ->where('estado_base', 1)
            ->latest()
            ->get();

        return view('cliente.encomiendas.index', compact('encomiendas'));
    }
}
