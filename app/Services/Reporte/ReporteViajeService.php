<?php

namespace App\Services\Reporte;

use App\Models\Viaje;

/**
 * Genera los datos y resumen del reporte de viajes.
 */
class ReporteViajeService
{
    /**
     * Obtiene los viajes dentro de un rango de fechas y aplica filtros opcionales.
     */
    public function viajesPorRangoFechas(string $desde, string $hasta, ?int $viajeId = null, ?string $buscar = null): array
    {
        $viajes = Viaje::activos()
            ->with(['ruta', 'bus'])
            ->withCount(['boletos as vendidos' => function ($query) {
                $query->where('estado_base', 1)->where('estado', 'confirmado');
            }])
            ->whereBetween('fecha_viaje', [$desde, $hasta])
            ->when($viajeId, fn ($query, $value) => $query->whereKey($value))
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->whereHas('ruta', function ($query) use ($buscar) {
                        $query->where('origen', 'like', "%{$buscar}%")
                            ->orWhere('destino', 'like', "%{$buscar}%");
                    })->orWhereHas('bus', fn ($query) => $query->where('placa', 'like', "%{$buscar}%"));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $capacidad = $viajes->sum(fn ($viaje) => $viaje->bus->capacidad ?? 0);
        $vendidos = $viajes->sum('vendidos');

        return [
            'viajes' => $viajes,
            'resumen' => [
                'total'     => $viajes->count(),
                'vendidos'  => $vendidos,
                'capacidad' => $capacidad,
                'ocupacion' => $capacidad > 0 ? round($vendidos * 100 / $capacidad, 1) : 0,
            ],
        ];
    }
}
