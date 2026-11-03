<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Encomienda;
use App\Models\Cliente;
use App\Models\TipoEncomienda;
use App\Models\Viaje;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EncomiendaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('encomiendas.ver');
        $request->validate([
            'search' => 'sometimes|nullable|string|max:255',
            'page' => 'sometimes|integer|min:1',
        ]);

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

    $datos = $request->validate([
        'cliente_id'             => ['nullable', 'integer', Rule::exists('clientes', 'id')->where('estado_base', 1)],
        'remitente_nombre'       => 'required_without:cliente_id|nullable|string|max:255',
        'remitente_ci'           => 'nullable|string|max:20',
        'remitente_telefono'     => 'nullable|string|max:20',
        'destinatario_nombre'    => 'required|string|max:255',
        'destinatario_ci'        => 'nullable|string|max:20',
        'destinatario_telefono'  => 'nullable|string|max:20',
        'viaje_id'               => ['required', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
        'tipo_encomienda_id'     => ['required', 'integer', Rule::exists('tipo_encomiendas', 'id')->where('estado_base', 1)],
        'cantidad'               => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($datos) {
        $tipo = TipoEncomienda::query()
            ->where('estado_base', 1)
            ->lockForUpdate()
            ->findOrFail($datos['tipo_encomienda_id']);
        $total = round((float) $tipo->precio * (int) $datos['cantidad'], 2);

        if ($total > 999999.99) {
            throw ValidationException::withMessages([
                'cantidad' => 'El total de la encomienda supera el máximo permitido.',
            ]);
        }

        $encomienda = Encomienda::create([
            'cliente_id'             => $datos['cliente_id'] ?? null,
            'remitente_nombre'       => $datos['remitente_nombre'] ?? null,
            'remitente_ci'           => $datos['remitente_ci'] ?? null,
            'remitente_telefono'     => $datos['remitente_telefono'] ?? null,
            'destinatario_nombre'    => $datos['destinatario_nombre'],
            'destinatario_ci'        => $datos['destinatario_ci'] ?? null,
            'destinatario_telefono'  => $datos['destinatario_telefono'] ?? null,
            'viaje_id'               => $datos['viaje_id'],
            'tipo_encomienda_id'     => $datos['tipo_encomienda_id'],
            'cantidad'               => $datos['cantidad'],
            'total_pagar'            => $total,
            'usuario_id'             => Auth::id(),
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
    });

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

        $datos = $request->validate([
            'cliente_id'             => ['nullable', 'integer', Rule::exists('clientes', 'id')->where('estado_base', 1)],
            'remitente_nombre'       => 'required_without:cliente_id|nullable|string|max:255',
            'remitente_ci'           => 'nullable|string|max:20',
            'remitente_telefono'     => 'nullable|string|max:20',
            'destinatario_nombre'    => 'required|string|max:255',
            'destinatario_ci'        => 'nullable|string|max:20',
            'destinatario_telefono'  => 'nullable|string|max:20',
            'viaje_id'               => ['required', 'integer', Rule::exists('viajes', 'id')->where('estado_base', 1)],
            'tipo_encomienda_id'     => ['required', 'integer', Rule::exists('tipo_encomiendas', 'id')->where('estado_base', 1)],
            'cantidad'               => 'required|integer|min:1',
            'estado'                 => 'required|in:recibido,en transito,entregado,cancelado',
        ]);

        DB::transaction(function () use ($datos, $encomienda) {
            $estadoAnterior = $encomienda->estado;
            $tipo = TipoEncomienda::query()
                ->where('estado_base', 1)
                ->lockForUpdate()
                ->findOrFail($datos['tipo_encomienda_id']);
            $total = round((float) $tipo->precio * (int) $datos['cantidad'], 2);

            if ($total > 999999.99) {
                throw ValidationException::withMessages([
                    'cantidad' => 'El total de la encomienda supera el máximo permitido.',
                ]);
            }

            $encomienda->update([
                'cliente_id'             => $datos['cliente_id'] ?? null,
                'remitente_nombre'       => $datos['remitente_nombre'] ?? null,
                'remitente_ci'           => $datos['remitente_ci'] ?? null,
                'remitente_telefono'     => $datos['remitente_telefono'] ?? null,
                'destinatario_nombre'    => $datos['destinatario_nombre'],
                'destinatario_ci'        => $datos['destinatario_ci'] ?? null,
                'destinatario_telefono'  => $datos['destinatario_telefono'] ?? null,
                'viaje_id'               => $datos['viaje_id'],
                'tipo_encomienda_id'     => $datos['tipo_encomienda_id'],
                'cantidad'               => $datos['cantidad'],
                'total_pagar'            => $total,
                'estado'                 => $datos['estado'],
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
        });

        return redirect()->route('transaccional.encomiendas.index')
                         ->with('success', 'Encomienda actualizada correctamente.');
    }

    public function imprimir(Encomienda $encomienda)
    {
        abort_unless(
            auth()->user()?->can('encomiendas.ver') || auth()->user()?->can('reportes.ver'),
            403
        );

        $encomienda->load(['viaje.ruta', 'viaje.bus', 'cliente', 'tipoEncomienda', 'usuario']);

        return view('transaccional.encomiendas.imprimir', compact('encomienda'));
    }

    public function destroy(Encomienda $encomienda)
    {
        $this->authorize('encomiendas.eliminar');

        DB::transaction(function () use ($encomienda) {
            $encomienda->update(['estado_base' => 0]);
        });

        return redirect()->route('transaccional.encomiendas.index')
                         ->with('success', 'Encomienda eliminada correctamente.');
    }
}