<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Boleto extends Model
{
    protected $fillable = [
        'reserva_id', 'viaje_id', 'numero_asiento',
        'nombre_pasajero', 'ci_pasajero', 'telefono_pasajero',
        'precio', 'metodo_pago', 'estado', 'expira_en',
        'comprobante_path',
    ];

    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',
            'expira_en' => 'datetime',
        ];
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->comprobante_path ? \Illuminate\Support\Facades\Storage::url($this->comprobante_path) : null;
    }

    // Verifica si el boleto pendiente ya expiró
    public function getEstaExpiradoAttribute(): bool
    {
        return $this->estado === 'pendiente'
            && $this->expira_en
            && Carbon::now()->isAfter($this->expira_en);
    }

    // Devuelve cuántos minutos faltan para expirar
    public function getMinutosRestantesAttribute(): int
    {
        if (!$this->expira_en || $this->estado !== 'pendiente') return 0;
        return max(0, (int) Carbon::now()->diffInMinutes($this->expira_en, false));
    }
}