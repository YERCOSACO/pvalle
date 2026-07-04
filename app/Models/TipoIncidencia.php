<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TipoIncidencia extends Model
{
    protected $table = 'tipo_incidencias';

    protected $fillable = [
        'nombre', 'descripcion', 'nivel_gravedad',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }
}