<?php

namespace App\Services\Reporte;

use App\Models\Encomienda;
use Illuminate\Support\Collection;

/**
 * ReporteEncomiendaService
 
 * Servicio para generación de reportes de Encomiendas
 * Encapsula la lógica de consultas y cálculos
 */
class ReporteEncomiendaService
{
    /**
     * RF17 - Encomiendas registradas dentro de un rango, filtradas opcionalmente por viaje y texto
     * Retorna encomiendas con relaciones cargadas para reportes
     */
    public function obtenerEncomiendas(string $desde, string $hasta, ?int $viajeId = null, ?string $buscar = null): Collection
    {
        $query = Encomienda::activos()
            ->with(['cliente', 'viaje.ruta', 'tipoEncomienda', 'usuario'])
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->when($viajeId, fn ($query, $value) => $query->where('viaje_id', $value))
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('remitente_nombre', 'like', "%{$buscar}%")
                        ->orWhere('remitente_ci', 'like', "%{$buscar}%")
                        ->orWhere('destinatario_nombre', 'like', "%{$buscar}%")
                        ->orWhere('destinatario_ci', 'like', "%{$buscar}%")
                        ->when(ctype_digit($buscar), fn ($query) => $query->orWhere('id', (int) $buscar))
                        ->orWhereHas('cliente', function ($query) use ($buscar) {
                            $query->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('apellido', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('tipoEncomienda', fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
                        ->orWhereHas('viaje.ruta', function ($query) use ($buscar) {
                            $query->where('origen', 'like', "%{$buscar}%")
                                ->orWhere('destino', 'like', "%{$buscar}%");
                        });
                });
            });

        return $query->orderByDesc('created_at')->orderByDesc('id')->get();
    }

    /**
     * Calcula estadísticas de encomiendas
     
     * @param Collection $encomiendas Colección de encomiendas
     * @return array Estadísticas: total, por estado, ingresos
     */
    public function calcularEstadisticas(Collection $encomiendas): array
    {
        return [
            'total' => $encomiendas->count(),
            'entregadas' => $encomiendas->where('estado', 'entregado')->count(),
            'en_transito' => $encomiendas->where('estado', 'en transito')->count(),
            'recibidas' => $encomiendas->where('estado', 'recibido')->count(),
            'canceladas' => $encomiendas->where('estado', 'cancelado')->count(),
            'ingresos_total' => $encomiendas->sum('total_pagar'),
            'cantidad_total' => $encomiendas->sum('cantidad'),
        ];
    }
}
