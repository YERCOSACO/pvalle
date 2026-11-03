<?php

namespace App\Http\Controllers\Reporte;

use App\Models\Viaje;
use App\Services\Reporte\ReporteViajeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteViajeController extends ReporteController
{
    public function __construct(
        private ReporteViajeService $reporteViajes,
    ) {
    }

    public function index(Request $request): View|Response
    {
        $this->authorize('reportes.ver');

        [$desde, $hasta] = $this->validarPeriodo($request);
        $filtros = $request->validate([
            'viaje_id' => ['nullable', 'integer', 'exists:viajes,id'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $viajeId = isset($filtros['viaje_id']) ? (int) $filtros['viaje_id'] : null;
        $buscar = $filtros['buscar'] ?? null;

        $datos = $this->reporteViajes->viajesPorRangoFechas($desde, $hasta, $viajeId, $buscar);
        $viajesDisponibles = Viaje::activos()
            ->with(['ruta', 'bus'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $vista = $request->boolean('imprimir') ? 'reporte.viajes.imprimir' : 'reporte.viajes.index';
        $viajeSeleccionado = $viajesDisponibles->firstWhere('id', $viajeId);

        return view($vista, $datos + compact('desde', 'hasta', 'viajeId', 'viajesDisponibles', 'viajeSeleccionado', 'buscar'));
    }
}
