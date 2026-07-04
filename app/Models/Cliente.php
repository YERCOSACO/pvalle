<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
}