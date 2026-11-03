<?php

namespace App\Http\Controllers\Estadisticas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class EstadisticasController extends Controller
{
    protected function periodo(Request $request): array
    {
        $filtros = $request->validate([
            'mes' => ['sometimes', 'integer', 'between:1,12'],
            'anio' => ['sometimes', 'integer', 'between:2020,2100'],
        ]);

        $mes = (int) ($filtros['mes'] ?? now()->month);
        $anio = (int) ($filtros['anio'] ?? now()->year);
        $desde = Carbon::create($anio, $mes, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();
        $mesAnterior = $desde->copy()->subMonthNoOverflow();

        return [$mes, $anio, $desde, $hasta, $mesAnterior];
    }
}
