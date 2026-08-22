<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Encomienda;
use App\Models\Cliente;
use App\Models\TipoEncomienda;
use App\Models\Viaje;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EncomiendaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('encomiendas.ver');

        $encomiendas = Encomienda::with('cliente', 'viaje.ruta', 'tipoEncomienda')
                                  ->where('estado_base', 1)
                                  ->when($request->search, function ($query, $search) {
                                      $query->where('destinatario_nombre', 'like', "%{$search}%")
                                            ->orWhere('remitente_nombre', 'like', "%{$search}%");
                                  })
                                  ->paginate(10)
                                  ->withQueryString();

        return view('transaccional.encomiendas.index', compact('encomiendas'));
    }

    public function create()
    {
        $this->authorize('encomiendas.crear');

        $clientes = Cliente::where('estado_base', 1)->get();
        $tiposEncomienda = TipoEncomienda::where('estado_base', 1)->get();
        $viajes = Viaje::with('ruta')->where('estado_base', 1)->get();

        return view('transaccional.encomiendas.create', compact('clientes', 'tiposEncomienda', 'viajes'));
    }

    public function store(Request $request)
{
    $this->authorize('encomiendas.crear');

    $request->validate([
        'cliente_id'             => 'nullable|exists:clientes,id',
        'remitente_nombre'       => 'required_without:cliente_id|nullable|string|max:255',
        'remitente_ci'           => 'nullable|string|max:20',
        'remitente_telefono'     => 'nullable|string|max:20',
        'destinatario_nombre'    => 'required|string|max:255',
        'destinatario_ci'        => 'nullable|string|max:20',
        'destinatario_telefono'  => 'nullable|string|max:20',
        'viaje_id'               => 'required|exists:viajes,id',
        'tipo_encomienda_id'     => 'required|exists:tipo_encomiendas,id',
        'cantidad'               => 'required|integer|min:1',
    ]);

    $tipo = TipoEncomienda::find($request->tipo_encomienda_id);
    $total = $tipo->precio * $request->cantidad;

    $encomienda = Encomienda::create([
        'cliente_id'             => $request->cliente_id,
        'remitente_nombre'       => $request->remitente_nombre,
        'remitente_ci'           => $request->remitente_ci,
        'remitente_telefono'     => $request->remitente_telefono,
        'destinatario_nombre'    => $request->destinatario_nombre,
        'destinatario_ci'        => $request->destinatario_ci,
        'destinatario_telefono'  => $request->destinatario_telefono,
        'viaje_id'               => $request->viaje_id,
        'tipo_encomienda_id'     => $request->tipo_encomienda_id,
        'cantidad'               => $request->cantidad,
        'total_pagar'            => $total,
    ]);

    if ($encomienda->cliente_id) {
        $encomienda->load('viaje');
        $fechaViaje = optional($encomienda->viaje)->fecha_viaje?->format('d/m/Y');

        \App\Http\Controllers\Transaccional\NotificacionController::crearAutomatica(
            $encomienda->cliente_id,
            'Tu encomienda fue recibida',
            'Tu paquete para ' . $encomienda->destinatario_nombre . ' fue recibido y viajará el ' . ($fechaViaje ?? 'próxima fecha') . '.',
            $encomienda,
            'info',
            'normal'
        );
    }

    return redirect()->route('transaccional.encomiendas.index')
                     ->with('success', 'Encomienda registrada correctamente.');
}

    public function edit(Encomienda $encomienda)
    {
        $this->authorize('encomiendas.editar');

        $clientes = Cliente::where('estado_base', 1)->get();
        $tiposEncomienda = TipoEncomienda::where('estado_base', 1)->get();
        $viajes = Viaje::with('ruta')->where('estado_base', 1)->get();

        return view('transaccional.encomiendas.edit', compact('encomienda', 'clientes', 'tiposEncomienda', 'viajes'));
    }

    public function update(Request $request, Encomienda $encomienda)
    {
        $this->authorize('encomiendas.editar');

        $request->validate([
            'cliente_id'             => 'nullable|exists:clientes,id',
            'remitente_nombre'       => 'required_without:cliente_id|nullable|string|max:255',
            'remitente_ci'           => 'nullable|string|max:20',
            'remitente_telefono'     => 'nullable|string|max:20',
            'destinatario_nombre'    => 'required|string|max:255',
            'destinatario_ci'        => 'nullable|string|max:20',
            'destinatario_telefono'  => 'nullable|string|max:20',
            'viaje_id'               => 'required|exists:viajes,id',
            'tipo_encomienda_id'     => 'required|exists:tipo_encomiendas,id',
            'cantidad'               => 'required|integer|min:1',
            'estado'                 => 'required|in:recibido,en transito,entregado,cancelado',
        ]);

        $estadoAnterior = $encomienda->estado;
        $tipo = TipoEncomienda::find($request->tipo_encomienda_id);
        $total = $tipo->precio * $request->cantidad;

        $encomienda->update([
            'cliente_id'             => $request->cliente_id,
            'remitente_nombre'       => $request->remitente_nombre,
            'remitente_ci'           => $request->remitente_ci,
            'remitente_telefono'     => $request->remitente_telefono,
            'destinatario_nombre'    => $request->destinatario_nombre,
            'destinatario_ci'        => $request->destinatario_ci,
            'destinatario_telefono'  => $request->destinatario_telefono,
            'viaje_id'               => $request->viaje_id,
            'tipo_encomienda_id'     => $request->tipo_encomienda_id,
            'cantidad'               => $request->cantidad,
            'total_pagar'            => $total,
            'estado'                 => $request->estado,
        ]);

        if ($encomienda->cliente_id && $estadoAnterior !== $encomienda->estado) {
            $encomienda->load('viaje.ruta');
            $fechaViaje = Carbon::parse($encomienda->viaje->fecha_viaje)->format('d/m/Y');
            \App\Http\Controllers\Transaccional\NotificacionController::crearAutomatica(
                $encomienda->cliente_id,
                'Cambio de estado de encomienda',
                "El estado de tu encomienda para {$encomienda->destinatario_nombre} cambió a {$encomienda->estado}. Viaje: {$encomienda->viaje->ruta->nombre_ruta} el {$fechaViaje} a las {$encomienda->viaje->hora_salida}.",
                $encomienda,
                'info',
                'normal'
            );
        }

        return redirect()->route('transaccional.encomiendas.index')
                         ->with('success', 'Encomienda actualizada correctamente.');
    }

    public function imprimir(Encomienda $encomienda)
    {
        $this->authorize('encomiendas.ver');

        $encomienda->load(['viaje.ruta', 'viaje.bus', 'cliente', 'tipoEncomienda']);

        return view('transaccional.encomiendas.imprimir', compact('encomienda'));
    }

    public function destroy(Encomienda $encomienda)
    {
        $this->authorize('encomiendas.eliminar');

        $encomienda->update(['estado_base' => 0]);

        return redirect()->route('transaccional.encomiendas.index')
                         ->with('success', 'Encomienda eliminada correctamente.');
    }
}