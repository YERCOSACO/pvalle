<?php

namespace App\Http\Controllers\Reporte;

use App\Services\Reporte\ReporteBoletoService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteBoletoController extends ReporteController
{
    public function __construct(
        private ReporteBoletoService $reporteBoletos,
    ) {
    }

    public function index(Request $request): View|Response
    {
        $this->authorize('reportes.ver');

        [$desde, $hasta] = $this->validarPeriodo($request);
        $buscar = $request->validate(['buscar' => ['nullable', 'string', 'max:100']])['buscar'] ?? null;
        $datos = $this->reporteBoletos->boletosPorRangoFechas($desde, $hasta, $buscar);

        $vista = $request->boolean('imprimir') ? 'reporte.boletos.imprimir' : 'reporte.boletos.index';

        return view($vista, $datos + compact('desde', 'hasta', 'buscar'));
    }
}
