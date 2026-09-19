<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Encomienda extends Model
{
    protected $fillable = [
        'cliente_id', 'remitente_nombre', 'remitente_ci', 'remitente_telefono',
        'destinatario_nombre', 'destinatario_ci', 'destinatario_telefono',
        'viaje_id', 'tipo_encomienda_id', 'cantidad', 'total_pagar', 'estado', 'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'total_pagar' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    public function tipoEncomienda()
    {
        return $this->belongsTo(TipoEncomienda::class);
    }

    public function getNombreRemitenteAttribute(): string
    {
        return $this->cliente?->nombre_completo ?? $this->remitente_nombre;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }
    public function notificaciones()
{
    return $this->morphMany(Notificacion::class, 'referenciable');
}
}