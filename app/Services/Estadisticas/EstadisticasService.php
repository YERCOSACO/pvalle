<?php

namespace App\Services\Estadisticas;

use App\Models\Boleto;
use App\Models\Encomienda;
use App\Models\Viaje;
use Illuminate\Support\Carbon;

class EstadisticasService
{
    public function detalleIngresos(Carbon $desde, Carbon $hasta): array
    {
        return app(EstadisticaIngresoService::class)->detalleIngresos($desde, $hasta);
    }

    public function viajesPorRuta(Carbon $desde, Carbon $hasta): array
    {
        return app(EstadisticaViajeRutaService::class)->viajesPorRuta($desde, $hasta);
    }

    public function detalleEncomiendas(Carbon $desde, Carbon $hasta): array
    {
        return app(EstadisticaEncomiendaService::class)->detalleEncomiendas($desde, $hasta);
    }

    public function resumenMensual(Carbon $desde, Carbon $hasta): array
    {
        $mesAnteriorDesde = $desde->copy()->startOfMonth()->subMonthNoOverflow();
        $mesAnteriorHasta = $mesAnteriorDesde->copy()->endOfMonth();

        $actual = $this->calcularPeriodo($desde, $hasta);
        $anterior = $this->calcularPeriodo($mesAnteriorDesde, $mesAnteriorHasta);

        return [
            'ingresos' => [
                'actual' => $actual['ingresos'],
                'anterior' => $anterior['ingresos'],
                'variacion' => $this->variacionPorcentual($actual['ingresos'], $anterior['ingresos']),
            ],
            'ocupacion' => [
                'actual' => $actual['ocupacion'],
                'anterior' => $anterior['ocupacion'],
                'cambio' => round($actual['ocupacion'] - $anterior['ocupacion'], 1),
                'boletos_confirmados' => $actual['boletos_confirmados'],
                'capacidad' => $actual['capacidad'],
            ],
            'incidencias_viaje' => [
                'actual' => $actual['viajes_cancelados_retrasados'],
                'anterior' => $anterior['viajes_cancelados_retrasados'],
                'cambio' => $actual['viajes_cancelados_retrasados'] - $anterior['viajes_cancelados_retrasados'],
                'cancelados' => $actual['viajes_cancelados'],
                'retrasados' => $actual['viajes_retrasados'],
                'cancelados_anterior' => $anterior['viajes_cancelados'],
                'retrasados_anterior' => $anterior['viajes_retrasados'],
            ],
            'entregas' => [
                'actual' => $actual['porcentaje_entregadas'],
                'anterior' => $anterior['porcentaje_entregadas'],
                'cambio' => round($actual['porcentaje_entregadas'] - $anterior['porcentaje_entregadas'], 1),
                'entregadas' => $actual['encomiendas_entregadas'],
                'total_no_canceladas' => $actual['encomiendas_no_canceladas'],
                'entregadas_anterior' => $anterior['encomiendas_entregadas'],
                'total_no_canceladas_anterior' => $anterior['encomiendas_no_canceladas'],
            ],
        ];
    }

    private function calcularPeriodo(Carbon $desde, Carbon $hasta): array
    {
        $ingresos = Boleto::activos()
            ->where('estado', 'confirmado')
            ->where(function ($query) use ($desde, $hasta) {
                $query->whereBetween('fecha_emision', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
                    ->orWhere(function ($query) use ($desde, $hasta) {
                        $query->whereNull('fecha_emision')
                            ->whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()]);
                    });
            })
            ->sum('precio');

        $viajes = Viaje::activos()
            ->with('bus')
            ->withCount(['boletos as boletos_confirmados' => fn ($query) => $query
                ->activos()
                ->where('estado', 'confirmado')])
            ->whereBetween('fecha_viaje', [$desde->toDateString(), $hasta->toDateString()])
            ->where('estado', '!=', 'cancelado')
            ->get();

        $capacidad = (int) $viajes->sum(fn (Viaje $viaje) => $viaje->bus?->capacidad ?? 0);
        $boletosConfirmados = (int) $viajes->sum('boletos_confirmados');
        $viajesCancelados = Viaje::activos()
            ->whereBetween('fecha_viaje', [$desde->toDateString(), $hasta->toDateString()])
            ->where('estado', 'cancelado')
            ->count();
        $viajesRetrasados = Viaje::activos()
            ->whereBetween('fecha_viaje', [$desde->toDateString(), $hasta->toDateString()])
            ->where('estado', 'retrasado')
            ->count();

        $encomiendas = Encomienda::activos()
            ->whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()]);
        $encomiendasEntregadas = (clone $encomiendas)->where('estado', 'entregado')->count();
        $encomiendasNoCanceladas = (clone $encomiendas)->where('estado', '!=', 'cancelado')->count();

        return [
            'ingresos' => (float) $ingresos,
            'ocupacion' => $capacidad > 0 ? round($boletosConfirmados * 100 / $capacidad, 1) : 0.0,
            'boletos_confirmados' => $boletosConfirmados,
            'capacidad' => $capacidad,
            'viajes_cancelados' => $viajesCancelados,
            'viajes_retrasados' => $viajesRetrasados,
            'viajes_cancelados_retrasados' => $viajesCancelados + $viajesRetrasados,
            'encomiendas_entregadas' => $encomiendasEntregadas,
            'encomiendas_no_canceladas' => $encomiendasNoCanceladas,
            'porcentaje_entregadas' => $encomiendasNoCanceladas > 0
                ? round($encomiendasEntregadas * 100 / $encomiendasNoCanceladas, 1)
                : 0.0,
        ];
    }

    private function variacionPorcentual(float $actual, float $anterior): ?float
    {
        if ($anterior === 0.0) {
            return $actual === 0.0 ? 0.0 : null;
        }

        return round(($actual - $anterior) * 100 / $anterior, 1);
    }
}
