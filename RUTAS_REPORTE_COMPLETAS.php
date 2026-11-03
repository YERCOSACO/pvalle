<?php
/**
 * RUTAS A AGREGAR EN routes/web.php
 * 
 * Copiar y pegar este grupo de rutas en tu archivo routes/web.php
 * dentro de un middleware de autenticación y verificación
 */

// Reportes - Agregar dentro de un grupo con middleware 'auth', 'verified'
Route::prefix('reportes')->name('reporte.')->group(function () {
    Route::get('boletos', [\App\Http\Controllers\Reporte\ReporteController::class, 'boletos'])->name('boletos');
    Route::get('viajes', [\App\Http\Controllers\Reporte\ReporteController::class, 'viajes'])->name('viajes');
    Route::get('encomiendas', [\App\Http\Controllers\Reporte\ReporteController::class, 'encomiendas'])->name('encomiendas');
});

/**
 * PERMISOS A VERIFICAR EN EL SEEDER
 * 
 * Asegúrate de que exista el permiso 'reportes.ver' en RolesAndPermissionsSeeder.php
 * 
 * Si no existe, agregar:
 * 
 * Permission::create(['name' => 'reportes.ver']);
 * 
 * Y asignarlo a los roles que deban tener acceso a reportes
 */
