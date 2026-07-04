<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Bus extends Model
{
    protected $fillable = [
        'placa', 'capacidad', 'modelo', 'tipo_bus',
    ];

    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
        ];
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }
}