<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;

class ReceptionAvailability
{
    // La señal caduca si la sesión de recepción deja de enviar actualizaciones.
    private const ACTIVE_WITHIN_SECONDS = 45;
    private const CACHE_KEY = 'receptionist_online';

    public function heartbeat(): void
    {
        Cache::put(self::CACHE_KEY, true, now()->addSeconds(self::ACTIVE_WITHIN_SECONDS));
    }
    public function isAvailable(): bool
    {
        return Cache::get(self::CACHE_KEY, false) === true;
    }
}
