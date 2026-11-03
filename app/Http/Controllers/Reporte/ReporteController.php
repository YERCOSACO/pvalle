<?php

namespace App\Http\Controllers\Reporte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

abstract class ReporteController extends Controller
{
    /**
     * Valida el rango de fechas y conserva compatibilidad con enlaces antiguos de mes/año.
     */
    protected function validarPeriodo(Request $request): array
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'between:2020,2100'],
        ]);

        if (isset($datos['desde'], $datos['hasta']) && $datos['hasta'] < $datos['desde']) {
            throw ValidationException::withMessages([
                'hasta' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            ]);
        }

        if (! $request->filled('desde') && ! $request->filled('hasta') && ($request->filled('mes') || $request->filled('anio'))) {
            $mes = (int) ($datos['mes'] ?? now()->month);
            $anio = (int) ($datos['anio'] ?? now()->year);
            $inicio = Carbon::create($anio, $mes, 1);

            return [$inicio->toDateString(), $inicio->copy()->endOfMonth()->toDateString()];
        }

        $desde = $datos['desde'] ?? null;
        $hasta = $datos['hasta'] ?? null;

        if ($desde && ! $hasta) {
            $hasta = Carbon::parse($desde)->endOfMonth()->toDateString();
        } elseif ($hasta && ! $desde) {
            $desde = Carbon::parse($hasta)->startOfMonth()->toDateString();
        } elseif (! $desde && ! $hasta) {
            $inicioAnio = now()->startOfYear();
            $desde = $inicioAnio->toDateString();
            $hasta = $inicioAnio->copy()->endOfYear()->toDateString();
        }

        return [$desde, $hasta];
    }
}
