<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Reserva;
use App\Models\Boleto;
use App\Models\Encomienda;
use App\Models\Notificacion;

class Cliente extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nombre', 'apellido', 'cedula', 'email',
        'contrasena', 'telefono', 'direccion', 'fecha_nacimiento',
    ];

    protected $hidden = [
        'contrasena', 'remember_token',
    ];

    // Le decimos a Laravel que la columna de contraseña se llama 'contrasena', no 'password'
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'contrasena'       => 'hashed',
        ];
    }

    // Scope para filtrar solo clientes activos
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_base', 1);
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function boletos(): HasManyThrough
    {
        return $this->hasManyThrough(Boleto::class, Reserva::class);
    }

    public function encomiendas(): HasMany
    {
        return $this->hasMany(Encomienda::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }
}