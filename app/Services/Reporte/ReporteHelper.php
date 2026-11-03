<?php

namespace App\Services\Reporte;

/**
 * Helper centralizado para datos compartidos en reportes
 * Evita duplicación de arrays y mantiene un único punto de actualización
 */
class ReporteHelper
{
    public static function meses(): array
    {
        return [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    public static function estados(): array
    {
        return [
            'programado'  => 'Programado',
            'en curso'    => 'En curso',
            'finalizado'  => 'Finalizado',
            'cancelado'   => 'Cancelado',
        ];
    }

    /**
     * Colores Tailwind para estados de boletos
     */
    public static function coloresBoletos(): array
    {
        return [
            'confirmado' => 'bg-green-100 text-green-700',
            'pendiente'  => 'bg-yellow-100 text-yellow-700',
            'cancelado'  => 'bg-red-100 text-red-700',
        ];
    }

    /**
     * Colores Tailwind para estados de viajes
     */
    public static function coloresViajes(): array
    {
        return [
            'programado'  => 'bg-blue-100 text-blue-700',
            'en curso'    => 'bg-yellow-100 text-yellow-700',
            'finalizado'  => 'bg-green-100 text-green-700',
            'cancelado'   => 'bg-red-100 text-red-700',
        ];
    }

    /**
     * Obtiene el nombre del mes por número
     */
    public static function nombreMes(int $mes): string
    {
        return self::meses()[$mes] ?? 'Inválido';
    }

    /**
     * Obtiene el nombre del estado del viaje
     */
    public static function nombreEstado(string $estado): string
    {
        return self::estados()[$estado] ?? ucfirst($estado);
    }

    /**
     * Obtiene array de estados de Encomienda
     */
    public static function estadosEncomiendas(): array
    {
        return [
            'recibido'     => 'Recibido',
            'en transito'  => 'En Tránsito',
            'entregado'    => 'Entregado',
            'cancelado'    => 'Cancelado',
        ];
    }

    /**
     * Colores Tailwind para estados de encomiendas
     */
    public static function coloresEncomiendas(): array
    {
        return [
            'recibido'     => 'bg-blue-100 text-blue-700',
            'en transito'  => 'bg-yellow-100 text-yellow-700',
            'entregado'    => 'bg-green-100 text-green-700',
            'cancelado'    => 'bg-red-100 text-red-700',
        ];
    }

    /**
     * Obtiene el nombre del estado de encomienda
     */
    public static function nombreEstadoEncomienda(string $estado): string
    {
        return self::estadosEncomiendas()[$estado] ?? ucfirst($estado);
    }
}
