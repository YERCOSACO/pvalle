<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'cliente_id', 'titulo', 'mensaje', 'tipo', 'canal', 'prioridad',
        'fecha_envio', 'fecha_lectura', 'referenciable_id', 'referenciable_type', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio'   => 'datetime',
            'fecha_lectura' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function referenciable()
    {
        return $this->morphTo();
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    // Accessor: descripción legible del origen, sin importar el tipo
    public function getOrigenLegibleAttribute(): string
    {
        return match ($this->referenciable_type) {
            \App\Models\IncidenciaViaje::class => 'Incidencia de viaje',
            \App\Models\Encomienda::class      => 'Encomienda',
          //  \App\Models\Reserva::class         => 'Reserva',
            default                            => 'General',
        };
    }
}