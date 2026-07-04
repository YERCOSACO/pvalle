<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AsientoViaje extends Model
{
    protected $table = 'asiento_viajes';

    protected $fillable = [
    'viaje_id',
    'numero_asiento',
    'estado',
    'estado_base'
];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }
}