<?php

namespace App\Services\Reporte;

use App\Models\Boleto;

/**
 * Genera los datos y resumen del reporte de boletos.
 */
class ReporteBoletoService
{
    /**
     * Obtiene los boletos emitidos dentro de un rango de fechas.
     */
    public function boletosPorRangoFechas(string $desde, string $hasta, ?string $buscar = null): array
    {
        $boletos = Boleto::activos()
            ->with(['viaje.ruta', 'viaje.bus'])
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre_pasajero', 'like', "%{$buscar}%")
                        ->orWhere('ci_pasajero', 'like', "%{$buscar}%")
                        ->orWhere('numero_asiento', 'like', "%{$buscar}%")
                        ->orWhereHas('viaje.ruta', function ($query) use ($buscar) {
                            $query->where('origen', 'like', "%{$buscar}%")
                                ->orWhere('destino', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $confirmados = $boletos->where('estado', 'confirmado');

        return [
            'boletos' => $boletos,
            'resumen' => [
                'total'       => $boletos->count(),
                'confirmados' => $confirmados->count(),
                'pendientes'  => $boletos->where('estado', 'pendiente')->count(),
                'cancelados'  => $boletos->where('estado', 'cancelado')->count(),
                'recaudado'   => $confirmados->sum('precio'),
            ],
        ];
    }
}
