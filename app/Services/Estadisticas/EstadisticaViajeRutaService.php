<?php

namespace App\Services\Estadisticas;

use App\Models\Viaje;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EstadisticaViajeRutaService
{
    public function viajesPorRuta(Carbon $desde, Carbon $hasta): array
    {
        $viajes = Viaje::activos()
            ->with(['ruta', 'bus'])
            ->withCount(['boletos as boletos_confirmados' => fn ($query) => $query
                ->activos()
                ->where('estado', 'confirmado')])
            ->whereBetween('fecha_viaje', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha_viaje')
            ->orderBy('hora_salida')
            ->get();

        $porRuta = $viajes->groupBy(fn (Viaje $viaje) => $viaje->ruta_id ?? 0)
            ->map(function (Collection $viajesRuta) {
                $viajesActivos = $viajesRuta->where('estado', '!=', 'cancelado');
                $capacidad = (int) $viajesActivos->sum(fn (Viaje $viaje) => $viaje->bus?->capacidad ?? 0);
                $confirmados = (int) $viajesActivos->sum('boletos_confirmados');

                return [
                    'nombre' => $viajesRuta->first()->ruta?->nombre_ruta ?? 'Sin ruta asignada',
                    'viajes' => $viajesRuta->count(),
                    'cancelados' => $viajesRuta->where('estado', 'cancelado')->count(),
                    'retrasados' => $viajesRuta->where('estado', 'retrasado')->count(),
                    'boletos_confirmados' => $confirmados,
                    'capacidad' => $capacidad,
                    'ocupacion' => $capacidad > 0 ? round($confirmados * 100 / $capacidad, 1) : 0.0,
                ];
            })
            ->sortByDesc('viajes')
            ->values();

        $viajesDetalle = $viajes->map(fn (Viaje $viaje) => [
            'ruta' => $viaje->ruta?->nombre_ruta ?? 'Sin ruta asignada',
            'fecha' => $viaje->fecha_viaje,
            'hora' => $viaje->hora_salida,
            'bus' => $viaje->bus?->placa ?? 'Sin bus',
            'estado' => $viaje->estado,
            'boletos_confirmados' => (int) $viaje->boletos_confirmados,
            'capacidad' => $viaje->estado === 'cancelado' ? 0 : (int) ($viaje->bus?->capacidad ?? 0),
        ]);

        $porEstado = $viajes->groupBy(fn (Viaje $viaje) => $viaje->estado ?: 'sin estado')
            ->map(fn (Collection $grupo, string $estado) => [
                'nombre' => ucfirst($estado),
                'cantidad' => $grupo->count(),
            ])
            ->sortByDesc('cantidad')
            ->values();

        $viajesPorDia = $viajes->groupBy(fn (Viaje $viaje) => $viaje->fecha_viaje->toDateString());
        $viajesDiarios = [];
        for ($dia = $desde->copy()->startOfDay(); $dia->lte($hasta); $dia->addDay()) {
            $viajesDiarios[] = [
                'dia' => $dia->day,
                'viajes' => $viajesPorDia->get($dia->toDateString(), collect())->count(),
            ];
        }

        return [
            'total_viajes' => $viajes->count(),
            'total_rutas' => $porRuta->count(),
            'por_ruta' => $porRuta,
            'por_estado' => $porEstado,
            'viajes' => $viajesDetalle,
            'por_dia' => collect($viajesDiarios),
        ];
    }
}
