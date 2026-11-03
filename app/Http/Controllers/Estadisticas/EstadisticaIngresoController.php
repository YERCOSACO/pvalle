<?php

namespace App\Http\Controllers\Estadisticas;

use App\Services\Estadisticas\EstadisticaIngresoService;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstadisticaIngresoController extends EstadisticasController
{
    public function index(Request $request, EstadisticaIngresoService $estadisticas, KpiService $kpis): View
    {
        $this->authorize('estadisticas.ver');

        [$mes, $anio, $desde, $hasta, $mesAnterior] = $this->periodo($request);
        $detalle = $estadisticas->detalleIngresos($desde, $hasta);
        $tarjetas = $kpis->ingresosPorBoletos($desde, $hasta);

        return view('estadisticas.ingresos', compact('anio', 'desde', 'hasta', 'mes', 'mesAnterior', 'detalle', 'tarjetas'));
    }
}
