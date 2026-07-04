<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenciaViaje extends Model
{
    protected $table = 'incidencia_viajes';

    protected $fillable = [
        'viaje_id', 'tipo_incidencia_id', 'fecha_inicio', 'fecha_fin', 'descripcion_detalle',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin'    => 'datetime',
        ];
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    public function tipoIncidencia()
    {
        return $this->belongsTo(TipoIncidencia::class);
    }
    public function notificaciones()
{
    return $this->morphMany(Notificacion::class, 'referenciable');
}
}