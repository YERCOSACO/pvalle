<?php

namespace App\Services\Estadisticas;

use App\Models\Encomienda;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EstadisticaEncomiendaService
{
    public function detalleEncomiendas(Carbon $desde, Carbon $hasta): array
    {
        $encomiendas = Encomienda::activos()
            ->with(['viaje.ruta', 'tipoEncomienda'])
            ->whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
            ->get();

        $porEstado = $encomiendas->groupBy(fn (Encomienda $encomienda) => $encomienda->estado ?: 'sin estado')
            ->map(fn (Collection $grupo, string $estado) => [
                'nombre' => ucfirst($estado),
                'cantidad' => $grupo->count(),
                'bultos' => (int) $grupo->sum('cantidad'),
                'monto' => (float) $grupo->sum('total_pagar'),
            ])
            ->sortByDesc('cantidad')
            ->values();

        $porRuta = $encomiendas->groupBy(fn (Encomienda $encomienda) => $encomienda->viaje?->ruta_id ?? 0)
            ->map(fn (Collection $grupo) => [
                'nombre' => $grupo->first()->viaje?->ruta?->nombre_ruta ?? 'Sin ruta asignada',
                'cantidad' => $grupo->count(),
                'bultos' => (int) $grupo->sum('cantidad'),
                'entregadas' => $grupo->where('estado', 'entregado')->count(),
                'monto' => (float) $grupo->sum('total_pagar'),
            ])
            ->sortByDesc('cantidad')
            ->values();

        $encomiendasPorDia = $encomiendas->groupBy(fn (Encomienda $encomienda) => $encomienda->created_at->toDateString());
        $encomiendasDiarias = [];
        for ($dia = $desde->copy()->startOfDay(); $dia->lte($hasta); $dia->addDay()) {
            $encomiendasDiarias[] = [
                'dia' => $dia->day,
                'cantidad' => $encomiendasPorDia->get($dia->toDateString(), collect())->count(),
            ];
        }

        return [
            'cantidad' => $encomiendas->count(),
            'bultos' => (int) $encomiendas->sum('cantidad'),
            'monto' => (float) $encomiendas->sum('total_pagar'),
            'entregadas' => $encomiendas->where('estado', 'entregado')->count(),
            'por_estado' => $porEstado,
            'por_ruta' => $porRuta,
            'por_dia' => collect($encomiendasDiarias),
        ];
    }
}
