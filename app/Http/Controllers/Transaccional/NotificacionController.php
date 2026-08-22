<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Cliente;
use App\Models\IncidenciaViaje;
use App\Models\Encomienda;
use App\Models\Boleto;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('notificaciones.ver');

        $notificaciones = Notificacion::with('cliente', 'referenciable')
                                       ->where('estado_base', 1)
                                       ->when($request->search, function ($query, $search) {
                                           $query->where('titulo', 'like', "%{$search}%")
                                                 ->orWhereHas('cliente', function ($q) use ($search) {
                                                     $q->where('nombre', 'like', "%{$search}%")
                                                       ->orWhere('apellido', 'like', "%{$search}%");
                                                 });
                                       })
                                       ->latest()
                                       ->paginate(10)
                                       ->withQueryString();

        return view('transaccional.notificaciones.index', compact('notificaciones'));
    }

    public function create()
    {
        $this->authorize('notificaciones.crear');

        $clientes = Cliente::where('estado_base', 1)->get();
        $incidencias = IncidenciaViaje::with('viaje.ruta')->where('estado_base', 1)->get();
        $encomiendas = Encomienda::where('estado_base', 1)->get();
        $boletos = Boleto::with('reserva.cliente', 'viaje.ruta')->where('estado_base', 1)->get();

        return view('transaccional.notificaciones.create', compact('clientes', 'incidencias', 'encomiendas', 'boletos'));
    }

    public function store(Request $request)
    {
        $this->authorize('notificaciones.crear');

        $request->validate([
            'cliente_id'         => 'required|exists:clientes,id',
            'titulo'             => 'required|string|max:255',
            'mensaje'            => 'required|string',
            'tipo'               => 'required|in:info,alerta,urgente',
            'canal'              => 'required|in:sistema,email,sms',
            'prioridad'          => 'required|in:baja,normal,alta',
            'origen_tipo'        => 'nullable|in:incidencia,encomienda,boleto',
            'origen_id'          => 'nullable|integer',
        ]);

        $referenciableType = match ($request->origen_tipo) {
            'incidencia' => IncidenciaViaje::class,
            'encomienda' => Encomienda::class,
            'boleto'     => Boleto::class,
            default      => null,
        };

        Notificacion::create([
            'cliente_id'         => $request->cliente_id,
            'titulo'             => $request->titulo,
            'mensaje'            => $request->mensaje,
            'tipo'               => $request->tipo,
            'canal'              => $request->canal,
            'prioridad'          => $request->prioridad,
            'fecha_envio'        => now(),
            'referenciable_id'   => $referenciableType ? $request->origen_id : null,
            'referenciable_type' => $referenciableType,
            'estado'             => 'enviada',
        ]);

        return redirect()->route('transaccional.notificaciones.index')
                         ->with('success', 'Notificación enviada correctamente.');
    }

    public function edit(Notificacion $notificacion)
    {
        $this->authorize('notificaciones.editar');

        $clientes = Cliente::where('estado_base', 1)->get();

        return view('transaccional.notificaciones.edit', compact('notificacion', 'clientes'));
    }

    public function update(Request $request, Notificacion $notificacion)
    {
        $this->authorize('notificaciones.editar');

        $request->validate([
            'titulo'    => 'required|string|max:255',
            'mensaje'   => 'required|string',
            'tipo'      => 'required|in:info,alerta,urgente',
            'prioridad' => 'required|in:baja,normal,alta',
            'estado'    => 'required|in:pendiente,enviada,leida',
        ]);

        $notificacion->update($request->only('titulo', 'mensaje', 'tipo', 'prioridad', 'estado'));

        return redirect()->route('transaccional.notificaciones.index')
                         ->with('success', 'Notificación actualizada correctamente.');
    }

    public function destroy(Notificacion $notificacion)
    {
        $this->authorize('notificaciones.eliminar');

        $notificacion->update(['estado_base' => 0]);

        return redirect()->route('transaccional.notificaciones.index')
                         ->with('success', 'Notificación eliminada correctamente.');
    }

    /**
     * Método reutilizable: cualquier controlador (IncidenciaViaje, Encomienda, Reserva)
     * puede llamar esto para crear una notificación automática, sin repetir código.
     */
    public static function crearAutomatica(
    int $clienteId,
    string $titulo,
    string $mensaje,
    \Illuminate\Database\Eloquent\Model $referenciable,
    string $tipo = 'info',
    string $prioridad = 'normal'
) {
    return Notificacion::create([
        'cliente_id'         => $clienteId,
        'titulo'             => $titulo,
        'mensaje'            => $mensaje,
        'tipo'               => $tipo,
        'canal'              => 'sistema',
        'prioridad'          => $prioridad,
        'fecha_envio'        => now(),
        'referenciable_id'   => $referenciable->id,
        'referenciable_type' => get_class($referenciable),
        'estado'             => 'enviada',
    ]);
}
}