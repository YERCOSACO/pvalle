<?php

namespace App\Http\Controllers\Transaccional;

use App\Http\Controllers\Controller;
use App\Models\IncidenciaViaje;
use App\Models\Notificacion;
use App\Models\Viaje;
use App\Models\TipoIncidencia;
use Illuminate\Http\Request;

class IncidenciaViajeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('incidenciaviaje.ver');

        $incidencias = IncidenciaViaje::with('viaje.ruta', 'tipoIncidencia')
                                       ->where('estado_base', 1)
                                       ->when($request->search, function ($query, $search) {
                                           $query->whereHas('tipoIncidencia', function ($q) use ($search) {
                                               $q->where('nombre', 'like', "%{$search}%");
                                           });
                                       })
                                       ->paginate(10)
                                       ->withQueryString();

        return view('transaccional.incidenciaviaje.index', compact('incidencias'));
    }

    public function create()
    {
        $this->authorize('incidenciaviaje.crear');

        $viajes = Viaje::where('estado_base', 1)->get();
        $tiposIncidencia = TipoIncidencia::where('estado_base', 1)->get();

        return view('transaccional.incidenciaviaje.create', compact('viajes', 'tiposIncidencia'));
    }

    public function store(Request $request)
    {
        $this->authorize('incidenciaviaje.crear');

        $request->validate([
            'viaje_id'            => 'required|exists:viajes,id',
            'tipo_incidencia_id'  => 'required|exists:tipo_incidencias,id',
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_detalle' => 'nullable|string',
        ]);

        $incidencia = IncidenciaViaje::create($request->only(
            'viaje_id', 'tipo_incidencia_id', 'fecha_inicio', 'fecha_fin', 'descripcion_detalle'
        ));

        $this->actualizarEstadoViajeYNotificar($incidencia);

        return redirect()->route('transaccional.incidenciaviaje.index')
                         ->with('success', 'Incidencia registrada correctamente.');
    }

    public function edit(IncidenciaViaje $incidenciaviaje)
    {
        $this->authorize('incidenciaviaje.editar');

        $viajes = Viaje::where('estado_base', 1)->get();
        $tiposIncidencia = TipoIncidencia::where('estado_base', 1)->get();

        return view('transaccional.incidenciaviaje.edit', compact('incidenciaviaje', 'viajes', 'tiposIncidencia'));
    }

    public function update(Request $request, IncidenciaViaje $incidenciaviaje)
    {
        $this->authorize('incidenciaviaje.editar');

        $request->validate([
            'viaje_id'            => 'required|exists:viajes,id',
            'tipo_incidencia_id'  => 'required|exists:tipo_incidencias,id',
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_detalle' => 'nullable|string',
        ]);

        $incidenciaviaje->update($request->only(
            'viaje_id', 'tipo_incidencia_id', 'fecha_inicio', 'fecha_fin', 'descripcion_detalle'
        ));

        $this->actualizarEstadoViajeYNotificar($incidenciaviaje);

        return redirect()->route('transaccional.incidenciaviaje.index')
                         ->with('success', 'Incidencia actualizada correctamente.');
    }

    public function destroy(IncidenciaViaje $incidenciaviaje)
    {
        $this->authorize('incidenciaviaje.eliminar');

        $incidenciaviaje->update(['estado_base' => 0]);

        return redirect()->route('transaccional.incidenciaviaje.index')
                         ->with('success', 'Incidencia eliminada correctamente.');
    }

    /**
     * Lógica simple: según la gravedad del tipo de incidencia,
     * cambia el estado del Viaje y notifica a los clientes con reservas en ese viaje.
     */
    private function actualizarEstadoViajeYNotificar(IncidenciaViaje $incidencia)
{
    $incidencia->load('viaje.ruta', 'tipoIncidencia');
    $viaje = $incidencia->viaje;
    $gravedad = $incidencia->tipoIncidencia->nivel_gravedad;

    $nuevoEstado = match ($gravedad) {
        'Leve'     => $viaje->estado,
        'Moderado' => 'retrasado',
        'Grave'    => 'cancelado',
        default    => $viaje->estado,
    };

    if ($nuevoEstado !== $viaje->estado) {
        $viaje->update(['estado' => $nuevoEstado]);
    }

    if (in_array($nuevoEstado, ['retrasado', 'cancelado'])) {
    /** @var \Carbon\Carbon $fechaViaje */
    $fechaViaje = $viaje->fecha_viaje;

    $mensaje = $nuevoEstado === 'cancelado'
        ? "Tu viaje del {$fechaViaje->format('d/m/Y')} ha sido CANCELADO por: {$incidencia->tipoIncidencia->nombre}."
        : "Tu viaje del {$fechaViaje->format('d/m/Y')} se encuentra RETRASADO por: {$incidencia->tipoIncidencia->nombre}.";

    // pendiente: notificar clientes cuando exista Boleto
}
        // Cuando exista Boleto, aquí se recorrerán los clientes reales con boleto en este viaje.
        // Por ahora, dejamos el método listo para usarse así:
        //
        // foreach ($viaje->clientesConBoleto() as $cliente) {
        //     NotificacionController::crearAutomatica(
        //         $cliente->id, 'Aviso de tu viaje', $mensaje, $incidencia, 'alerta', 'alta'
        //     );
        // }
    
}
}