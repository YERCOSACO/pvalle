<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Boleto extends Model
{
    public const TIEMPO_PAGO_QR_MINUTOS = 15;

    protected $fillable = [
        'reserva_id', 'viaje_id', 'numero_asiento',
        'nombre_pasajero', 'ci_pasajero', 'telefono_pasajero',
        'precio', 'metodo_pago', 'estado', 'expira_en',
        'comprobante_path', 'espacio_extra', 'mascota',
        'fecha_emision', 'usuario_id', 'cliente_emisor_id',
    ];

    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',
            'expira_en' => 'datetime',
            'fecha_emision' => 'datetime',
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

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function clienteEmisor()
    {
        return $this->belongsTo(Cliente::class, 'cliente_emisor_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('estado_base'), 1);
    }

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->comprobante_path ? \Illuminate\Support\Facades\Storage::url($this->comprobante_path) : null;
    }

    // Verifica si el boleto pendiente ya expiró
    public function getEstaExpiradoAttribute(): bool
    {
        return $this->estado === 'pendiente'
            && $this->expira_en !== null
            && $this->expira_en->lessThanOrEqualTo(now());
    }

    public function getSegundosRestantesAttribute(): int
    {
        if (! $this->expira_en || $this->estado !== 'pendiente') {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->expira_en, false));
    }

    public function getMinutosRestantesAttribute(): int
    {
        return (int) ceil($this->segundos_restantes / 60);
    }
}