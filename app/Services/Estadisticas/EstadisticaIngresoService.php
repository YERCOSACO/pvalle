<?php

namespace App\Services\Estadisticas;

use App\Models\Boleto;
use Illuminate\Support\Carbon;

class EstadisticaIngresoService
{
    public function detalleIngresos(Carbon $desde, Carbon $hasta): array
    {
        $boletos = Boleto::query()
            ->activos()
            ->where('boletos.estado', 'confirmado')
            ->where(function ($query) use ($desde, $hasta) {
                $query->whereBetween('boletos.fecha_emision', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
                    ->orWhere(function ($query) use ($desde, $hasta) {
                        $query->whereNull('boletos.fecha_emision')
                            ->whereBetween('boletos.created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()]);
                    });
            });

        $porRuta = (clone $boletos)
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->select('rutas.id as ruta_id', 'rutas.origen', 'rutas.destino')
            ->selectRaw('COUNT(boletos.id) as boletos, SUM(boletos.precio) as ingresos')
            ->groupBy('rutas.id', 'rutas.origen', 'rutas.destino')
            ->orderByDesc('ingresos')
            ->get()
            ->map(fn ($fila) => [
                'nombre' => $fila->ruta_id ? "{$fila->origen} → {$fila->destino}" : 'Sin ruta asignada',
                'boletos' => (int) $fila->boletos,
                'ingresos' => (float) $fila->ingresos,
            ]);

        $porMetodo = (clone $boletos)
            ->select('metodo_pago')
            ->selectRaw('COUNT(*) as boletos, SUM(boletos.precio) as ingresos')
            ->groupBy('boletos.metodo_pago')
            ->orderByDesc('ingresos')
            ->get()
            ->map(fn ($fila) => [
                'nombre' => $fila->metodo_pago ?: 'No especificado',
                'boletos' => (int) $fila->boletos,
                'ingresos' => (float) $fila->ingresos,
            ]);

        $porDia = (clone $boletos)
            ->selectRaw('DATE(COALESCE(boletos.fecha_emision, boletos.created_at)) as dia')
            ->selectRaw('COUNT(*) as boletos, SUM(boletos.precio) as ingresos')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $ingresosDiarios = [];
        for ($dia = $desde->copy()->startOfDay(); $dia->lte($hasta); $dia->addDay()) {
            $registro = $porDia->get($dia->toDateString());
            $ingresosDiarios[] = [
                'dia' => $dia->day,
                'boletos' => (int) ($registro->boletos ?? 0),
                'ingresos' => (float) ($registro->ingresos ?? 0),
            ];
        }

        return [
            'total' => (float) $porRuta->sum('ingresos'),
            'boletos' => (int) $porRuta->sum('boletos'),
            'promedio_boleto' => $porRuta->sum('boletos') > 0
                ? round($porRuta->sum('ingresos') / $porRuta->sum('boletos'), 2)
                : 0.0,
            'por_ruta' => $porRuta,
            'por_metodo' => $porMetodo,
            'por_dia' => collect($ingresosDiarios),
        ];
    }
}
