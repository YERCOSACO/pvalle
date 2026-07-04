<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Conductor extends Model
{
    protected $table = 'conductores';

    protected $fillable = [
        'nombre', 'apellido', 'licencia', 'telefono',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }
}