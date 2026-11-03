<?php

namespace App\Http\Controllers\Estadisticas;

use App\Services\Estadisticas\EstadisticaEncomiendaService;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstadisticaEncomiendaController extends EstadisticasController
{
    public function index(Request $request, EstadisticaEncomiendaService $estadisticas, KpiService $kpis): View
    {
        $this->authorize('estadisticas.ver');

        [$mes, $anio, $desde, $hasta, $mesAnterior] = $this->periodo($request);
        $detalle = $estadisticas->detalleEncomiendas($desde, $hasta);
        $tarjetas = $kpis->encomiendas($desde, $hasta);

        return view('estadisticas.encomiendas', compact('anio', 'desde', 'hasta', 'mes', 'mesAnterior', 'detalle', 'tarjetas'));
    }
}
