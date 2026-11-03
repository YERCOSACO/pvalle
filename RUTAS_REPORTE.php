// Agregar estas líneas en routes/web.php dentro del middleware autenticado

Route::middleware(['auth', 'verified'])->name('reporte.')->group(function () {
    Route::get('reportes/boletos', [\App\Http\Controllers\Reporte\ReporteController::class, 'boletos'])->name('boletos');
    Route::get('reportes/viajes', [\App\Http\Controllers\Reporte\ReporteController::class, 'viajes'])->name('viajes');
});
