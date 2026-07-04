<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Seguridad\RolController;
use App\Http\Controllers\Seguridad\UsuarioRolController;
use App\Http\Controllers\Parametrizacion\ClienteController;
use App\Http\Controllers\Parametrizacion\BusController;
use App\Http\Controllers\Parametrizacion\ConductorController;
use App\Http\Controllers\Parametrizacion\RutaController;
use App\Http\Controllers\Parametrizacion\TipoEncomiendaController;
use App\Http\Controllers\Parametrizacion\TipoIncidenciaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transaccional\ViajeController;
use App\Http\Controllers\Transaccional\AsignacionConductorController;
use App\Http\Controllers\Transaccional\IncidenciaViajeController;
use App\Http\Controllers\Transaccional\EncomiendaController;
use App\Http\Controllers\Transaccional\NotificacionController;
use App\Http\Controllers\Transaccional\AsientoViajeController;
use App\Http\Controllers\Transaccional\ReservaController;
use App\Http\Controllers\Transaccional\BoletoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── MÓDULO SEGURIDAD ──────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('seguridad')->name('seguridad.')->group(function () {

    Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario']);
    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
    Route::resource('usuariorol', UsuarioRolController::class)->parameters(['usuariorol' => 'usuario'])->except(['show']);
});

// ── MÓDULO PARAMETRIZACIÓN ───────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('parametrizacion')->name('parametrizacion.')->group(function () {

    Route::resource('clientes', ClienteController::class)->parameters(['clientes' => 'cliente']);
    Route::resource('buses', BusController::class)->parameters(['buses' => 'bus']);
    Route::resource('conductores', ConductorController::class)->parameters(['conductores' => 'conductor']);
    Route::resource('rutas', RutaController::class)->parameters(['rutas' => 'ruta']);
    Route::resource('tipoencomiendas', TipoEncomiendaController::class)->parameters(['tipoencomiendas' => 'tipoEncomienda']);
    Route::resource('tipoincidencias', TipoIncidenciaController::class)->parameters(['tipoincidencias' => 'tipoIncidencia']);
});

Route::middleware(['auth', 'verified'])->prefix('transaccional')->name('transaccional.')->group(function () {

    Route::resource('viajes', ViajeController::class)->parameters(['viajes' => 'viaje']);
    Route::resource('asignacionconductor', AsignacionConductorController::class)->parameters(['asignacionconductor' => 'viaje'])->except(['show']);
    Route::resource('incidenciaviaje', IncidenciaViajeController::class)->parameters(['incidenciaviaje' => 'incidenciaviaje']);
    Route::resource('encomiendas', EncomiendaController::class)->parameters(['encomiendas' => 'encomienda']);
    Route::resource('notificaciones', NotificacionController::class)->parameters(['notificaciones' => 'notificacion']);
    Route::resource('asientoviaje', AsientoViajeController::class)->parameters(['asientoviaje' => 'asientoviaje']);
    Route::resource('reservas', ReservaController::class)->parameters(['reservas' => 'reserva']);

    // ← Estas 2 ANTES del resource de boletos
    Route::get('boletos/asientos/{viaje}', [BoletoController::class, 'asientosDisponibles'])
         ->name('boletos.asientos-disponibles');
    Route::patch('boletos/{boleto}/confirmar-pago', [BoletoController::class, 'confirmarPago'])
         ->name('boletos.confirmar-pago');

    Route::resource('boletos', BoletoController::class)->parameters(['boletos' => 'boleto']);
    Route::get('boletos/viaje/{viaje}', [BoletoController::class, 'porViaje'])
     ->name('boletos.por-viaje');
    });
    Route::get(
    'transaccional/boletos/{boleto}/imprimir',
    [BoletoController::class, 'imprimir']
)->name('transaccional.boletos.imprimir');
require __DIR__.'/auth.php';