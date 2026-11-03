<?php

namespace App\Http\Controllers\Estadisticas;

use App\Services\Estadisticas\EstadisticaViajeRutaService;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstadisticaViajeRutaController extends EstadisticasController
{
    public function index(Request $request, EstadisticaViajeRutaService $estadisticas, KpiService $kpis): View
    {
        $this->authorize('estadisticas.ver');

        [$mes, $anio, $desde, $hasta, $mesAnterior] = $this->periodo($request);
        $detalle = $estadisticas->viajesPorRuta($desde, $hasta);
        $tarjetas = $kpis->viajesPorRutas($desde, $hasta);

        return view('estadisticas.viajes-ruta', compact('anio', 'desde', 'hasta', 'mes', 'mesAnterior', 'detalle', 'tarjetas'));
    }
}
