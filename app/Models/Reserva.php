<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Reserva extends Model
{
    protected $fillable = [
        'cliente_id', 'fecha_reserva', 'cantidad', 'total_pagar', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reserva' => 'datetime',
            'total_pagar'   => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    // Cuántos boletos faltan llenar todavía
    public function getBoletosPendientesAttribute(): int
    {
        return $this->cantidad - $this->boletos->count();
    }
}