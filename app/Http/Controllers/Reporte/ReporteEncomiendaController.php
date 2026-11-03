<?php

namespace App\Http\Controllers\Reporte;

use App\Models\Viaje;
use App\Services\Reporte\ReporteEncomiendaService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteEncomiendaController extends ReporteController
{
    public function __construct(
        private ReporteEncomiendaService $reporteEncomiendas,
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

        $encomiendas = $this->reporteEncomiendas->obtenerEncomiendas($desde, $hasta, $viajeId, $buscar);
        $viajesDisponibles = Viaje::activos()
            ->with(['ruta', 'bus'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $vista = $request->boolean('imprimir') ? 'reporte.encomiendas.imprimir' : 'reporte.encomiendas.index';
        $viajeSeleccionado = $viajesDisponibles->firstWhere('id', $viajeId);

        return view($vista, compact('encomiendas', 'desde', 'hasta', 'viajeId', 'viajesDisponibles', 'viajeSeleccionado', 'buscar'));
    }
}
