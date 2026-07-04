<?php

use App\Models\User;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ── INICIO BLOQUE JWT (tarea/práctica) ──────────────
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
        // ── FIN BLOQUE JWT ───────────────────────────────────

        // ── INICIO BLOQUE GUARD CLIENTE ──────────────────────
        'cliente' => [
            'driver' => 'session',
            'provider' => 'clientes',
        ],
        // ── FIN BLOQUE GUARD CLIENTE ──────────────────────────
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // ── INICIO BLOQUE GUARD CLIENTE ──────────────────────
        'clientes' => [
            'driver' => 'eloquent',
            'model' => App\Models\Cliente::class,
        ],
        // ── FIN BLOQUE GUARD CLIENTE ──────────────────────────
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];