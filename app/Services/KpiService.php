<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function ingresosPorBoletos(Carbon $desde, Carbon $hasta): array
    {
        $actual = $this->resumenBoletos($desde, $hasta);
        $anterior = $this->resumenBoletos($desde->copy()->subMonthNoOverflow()->startOfMonth(), $desde->copy()->subMonthNoOverflow()->endOfMonth());
        $viajes = $this->resumenViajes($desde, $hasta);
        $tasaPago = $this->tasaPago($desde, $hasta);
        $ocupacion = $this->porcentaje($viajes['boletos_confirmados'], $viajes['capacidad']);
        $ingresoPorViaje = $this->division($actual['ingresos'], $viajes['viajes_no_cancelados']);

        return [
            $this->tarjeta(
                'Ingresos confirmados',
                $actual['boletos'] > 0 ? $actual['ingresos'] : null,
                number_format($actual['boletos'], 0, ',', '.').' boletos vendidos',
                'moneda',
                'Suma del precio de boletos activos confirmados por su fecha de emisión, comparado con el mes anterior.',
                $this->variacion($actual['ingresos'], $anterior['ingresos'])
            ),
            $this->tarjeta(
                'Ocupación promedio',
                $ocupacion,
                'Boletos confirmados ÷ capacidad de viajes no cancelados',
                'porcentaje',
                'Boletos confirmados para los viajes del periodo divididos entre sus asientos disponibles.'
            ),
            $this->tarjeta(
                'Ingreso por viaje',
                $ingresoPorViaje,
                'Ingresos confirmados ÷ viajes no cancelados',
                'moneda',
                'Ingresos confirmados del periodo divididos entre los viajes no cancelados.'
            ),
            $this->tarjeta(
                'Tasa de pago',
                $tasaPago,
                'Confirmados ÷ (confirmados + cancelados + pendientes vencidos)',
                'porcentaje',
                'Cuenta boletos activos confirmados, cancelados y pendientes cuya fecha expira_en ya venció dentro del periodo.'
            ),
        ];
    }

    public function viajesPorRutas(Carbon $desde, Carbon $hasta): array
    {
        $actual = $this->resumenViajes($desde, $hasta);
        $anterior = $this->resumenViajes($desde->copy()->subMonthNoOverflow()->startOfMonth(), $desde->copy()->subMonthNoOverflow()->endOfMonth());
        $ocupacion = $this->porcentaje($actual['boletos_confirmados'], $actual['capacidad']);
        $sinIncidencias = $this->porcentaje(
            $actual['total'] - $actual['retrasados'] - $actual['cancelados'],
            $actual['total']
        );

        return [
            $this->tarjeta(
                'Viajes del periodo',
                $actual['total'] > 0 ? $actual['total'] : null,
                number_format($actual['rutas'], 0, ',', '.').' rutas con actividad',
                'entero',
                'Cantidad de viajes activos registrados en el periodo, comparada con el mes anterior.',
                $this->variacion($actual['total'], $anterior['total'])
            ),
            $this->tarjeta(
                'Ocupación global',
                $ocupacion,
                'Boletos confirmados ÷ capacidad de viajes no cancelados',
                'porcentaje',
                'Boletos confirmados para los viajes no cancelados divididos entre su capacidad total.'
            ),
            $this->tarjeta(
                'Viajes sin incidencias',
                $sinIncidencias,
                '(Viajes − retrasados − cancelados) ÷ viajes',
                'porcentaje',
                'Porcentaje de viajes activos que no están marcados como retrasados ni cancelados.'
            ),
            $this->tarjeta(
                'Asientos vacíos',
                $actual['capacidad'] > 0 ? $actual['capacidad'] - $actual['boletos_confirmados'] : null,
                'Capacidad de viajes no cancelados − boletos confirmados',
                'entero',
                'Asientos totales de viajes no cancelados menos boletos confirmados.'
            ),
        ];
    }

    public function encomiendas(Carbon $desde, Carbon $hasta): array
    {
        $actual = $this->resumenEncomiendas($desde, $hasta);
        $anterior = $this->resumenEncomiendas($desde->copy()->subMonthNoOverflow()->startOfMonth(), $desde->copy()->subMonthNoOverflow()->endOfMonth());
        $ingresosBoletos = $this->resumenBoletos($desde, $hasta)['ingresos'];
        $totalIngresosRegistrados = $ingresosBoletos + $actual['monto'];
        $participacionIngresos = $this->porcentaje($actual['monto'], $totalIngresosRegistrados);
        $entregas = $actual['registradas'] - $actual['canceladas'];

        return [
            $this->tarjeta(
                'Encomiendas registradas',
                $actual['registradas'] > 0 ? $actual['registradas'] : null,
                number_format($actual['bultos'], 0, ',', '.').' bultos',
                'entero',
                'Cantidad de encomiendas activas registradas en el periodo, comparada con el mes anterior.',
                $this->variacion($actual['registradas'], $anterior['registradas'])
            ),
            $this->tarjeta(
                'Tasa de entrega',
                $this->porcentaje($actual['entregadas'], $entregas),
                'Entregadas ÷ (registradas − canceladas)',
                'porcentaje',
                'Encomiendas entregadas divididas entre las registradas que no fueron canceladas.'
            ),
            $this->tarjeta(
                'Pendientes en tránsito',
                $actual['registradas'] > 0
                    ? $actual['registradas'] - $actual['entregadas'] - $actual['canceladas']
                    : null,
                'Registradas − entregadas − canceladas',
                'entero',
                'Encomiendas registradas que todavía no fueron entregadas ni canceladas; incluye las recibidas.'
            ),
            $this->tarjeta(
                'Ingreso registrado por encomiendas',
                $actual['registradas'] > $actual['canceladas'] ? $actual['monto'] : null,
                $actual['registradas'] === 0
                    ? 'Sin datos del periodo'
                    : ($participacionIngresos === null
                        ? 'Sin base para calcular el porcentaje del ingreso total'
                        : number_format($participacionIngresos, 1, ',', '.').'% del ingreso total registrado'),
                'moneda',
                'Suma de total_pagar en encomiendas no canceladas. Este monto no confirma que se haya cobrado.'
            ),
        ];
    }

    private function resumenBoletos(Carbon $desde, Carbon $hasta): array
    {
        $totales = DB::table('boletos')
            ->where('boletos.estado_base', 1)
            ->where('boletos.estado', 'confirmado')
            ->whereRaw(
                'COALESCE(boletos.fecha_emision, boletos.created_at) BETWEEN ? AND ?',
                [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()]
            )
            ->selectRaw('COUNT(*) as boletos, COALESCE(SUM(boletos.precio), 0) as ingresos')
            ->first();

        return [
            'boletos' => (int) $totales->boletos,
            'ingresos' => (float) $totales->ingresos,
        ];
    }

    private function resumenViajes(Carbon $desde, Carbon $hasta): array
    {
        $boletosConfirmados = DB::table('boletos')
            ->select('viaje_id')
            ->selectRaw('COUNT(*) as cantidad')
            ->where('estado_base', 1)
            ->where('estado', 'confirmado')
            ->groupBy('viaje_id');

        $totales = DB::table('viajes')
            ->join('buses', function ($join) {
                $join->on('buses.id', '=', 'viajes.bus_id')
                    ->where('buses.estado_base', 1);
            })
            ->leftJoinSub($boletosConfirmados, 'boletos_confirmados', function ($join) {
                $join->on('boletos_confirmados.viaje_id', '=', 'viajes.id');
            })
            ->where('viajes.estado_base', 1)
            ->whereBetween('viajes.fecha_viaje', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('COUNT(viajes.id) as total')
            ->selectRaw('COUNT(DISTINCT viajes.ruta_id) as rutas')
            ->selectRaw("SUM(CASE WHEN viajes.estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados")
            ->selectRaw("SUM(CASE WHEN viajes.estado = 'retrasado' THEN 1 ELSE 0 END) as retrasados")
            ->selectRaw("SUM(CASE WHEN viajes.estado != 'cancelado' THEN buses.capacidad ELSE 0 END) as capacidad")
            ->selectRaw("SUM(CASE WHEN viajes.estado != 'cancelado' THEN COALESCE(boletos_confirmados.cantidad, 0) ELSE 0 END) as boletos_confirmados")
            ->selectRaw("SUM(CASE WHEN viajes.estado != 'cancelado' THEN 1 ELSE 0 END) as viajes_no_cancelados")
            ->first();

        return [
            'total' => (int) $totales->total,
            'rutas' => (int) $totales->rutas,
            'cancelados' => (int) $totales->cancelados,
            'retrasados' => (int) $totales->retrasados,
            'capacidad' => (int) $totales->capacidad,
            'boletos_confirmados' => (int) $totales->boletos_confirmados,
            'viajes_no_cancelados' => (int) $totales->viajes_no_cancelados,
        ];
    }

    private function resumenEncomiendas(Carbon $desde, Carbon $hasta): array
    {
        $totales = DB::table('encomiendas')
            ->where('estado_base', 1)
            ->whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
            ->selectRaw('COUNT(*) as registradas')
            ->selectRaw('COALESCE(SUM(cantidad), 0) as bultos')
            ->selectRaw("SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregadas")
            ->selectRaw("SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas")
            ->selectRaw("COALESCE(SUM(CASE WHEN estado != 'cancelado' THEN total_pagar ELSE 0 END), 0) as monto")
            ->first();

        return [
            'registradas' => (int) $totales->registradas,
            'bultos' => (int) $totales->bultos,
            'entregadas' => (int) $totales->entregadas,
            'canceladas' => (int) $totales->canceladas,
            'monto' => (float) $totales->monto,
        ];
    }

    private function tasaPago(Carbon $desde, Carbon $hasta): ?float
    {
        $inicio = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->endOfDay();

        $confirmados = DB::table('boletos')
            ->where('estado_base', 1)
            ->where('estado', 'confirmado')
            ->whereRaw('COALESCE(fecha_emision, created_at) BETWEEN ? AND ?', [$inicio, $fin])
            ->count();

        $cancelados = DB::table('boletos')
            ->where('estado_base', 1)
            ->where('estado', 'cancelado')
            ->whereBetween('updated_at', [$inicio, $fin])
            ->count();

        $vencidos = DB::table('boletos')
            ->where('estado_base', 1)
            ->where('estado', 'pendiente')
            ->whereNotNull('expira_en')
            ->whereBetween('expira_en', [$inicio, min($fin, now())])
            ->count();

        return $this->porcentaje($confirmados, $confirmados + $cancelados + $vencidos);
    }

    private function porcentaje(float|int $numerador, float|int $denominador): ?float
    {
        return $denominador > 0 ? round($numerador * 100 / $denominador, 1) : null;
    }

    private function division(float|int $numerador, float|int $denominador): ?float
    {
        return $denominador > 0 ? round($numerador / $denominador, 2) : null;
    }

    private function variacion(float|int $actual, float|int $anterior): ?float
    {
        return $anterior > 0 ? round(($actual - $anterior) * 100 / $anterior, 1) : null;
    }

    private function tarjeta(
        string $titulo,
        float|int|null $valor,
        string $subtitulo,
        string $formato,
        string $ayuda,
        ?float $variacion = null
    ): array {
        return compact('titulo', 'valor', 'subtitulo', 'formato', 'ayuda', 'variacion');
    }
}
