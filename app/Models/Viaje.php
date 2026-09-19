<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Viaje extends Model
{
    protected $fillable = [
        'ruta_id', 'bus_id', 'fecha_viaje', 'hora_salida', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_viaje' => 'date',
        ];
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    public function scopeReservables(Builder $query): Builder
    {
        return $query->activos()
            ->where('estado', 'programado')
            ->whereDate('fecha_viaje', '>=', Carbon::today())
            ->whereHas('asientos', function (Builder $asientos) {
                $asientos->activos()->where('estado', 'disponible');
            });
    }

    public function scopeConConductorAsignado(Builder $query): Builder
    {
        return $query->whereHas('asignaciones', function (Builder $asignaciones) {
            $asignaciones->where('tipo_asignacion', 'Principal')
                         ->where('estado_base', 1);
        });
    }

    public function asignaciones()
    {
    return $this->hasMany(AsignacionConductor::class);
    }
    public function asientos()
    {
    return $this->hasMany(AsientoViaje::class);
    }
    public function boletos()
{
    return $this->hasMany(Boleto::class);
}
}