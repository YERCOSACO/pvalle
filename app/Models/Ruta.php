<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ruta extends Model
{
    protected $fillable = [
        'origen', 'destino', 'distancia_km', 'precio_base',
    ];

    protected function casts(): array
    {
        return [
            'distancia_km' => 'decimal:2',
            'precio_base'  => 'decimal:2',
        ];
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    // Accessor útil: muestra la ruta como "Santa Cruz → La Paz"
    public function getNombreRutaAttribute(): string
    {
        return "{$this->origen} → {$this->destino}";
    }
}