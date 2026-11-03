<?php

use Illuminate\Foundation\Inspiring;
use App\Services\ViajeAsientosService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('boletos:liberar-vencidos', function (ViajeAsientosService $asientosService) {
    $liberados = $asientosService->liberarVencidos();
    $this->info("Boletos vencidos liberados: {$liberados}.");
})->purpose('Libera asientos de boletos QR cuyo plazo de pago venció');

Schedule::command('boletos:liberar-vencidos')->everyMinute()->withoutOverlapping();
