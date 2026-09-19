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
use App\Http\Controllers\Cliente\DashboardController;
use App\Http\Controllers\Cliente\ReservaController as ClienteReservaController;
use App\Http\Controllers\Cliente\BoletoController as ClienteBoletoController;
use App\Http\Controllers\Cliente\EncomiendaController as ClienteEncomiendaController;
use App\Http\Controllers\Cliente\NotificacionController as ClienteNotificacionController;
use App\Http\Controllers\Auth\ClienteAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transaccional\ViajeController;
use App\Http\Controllers\Transaccional\AsignacionConductorController;
use App\Http\Controllers\Transaccional\IncidenciaViajeController;
use App\Http\Controllers\Transaccional\EncomiendaController;
use App\Http\Controllers\Transaccional\NotificacionController;
use App\Http\Controllers\Parametrizacion\AsientoViajeController;
use App\Http\Controllers\Transaccional\ReservaController;
use App\Http\Controllers\Transaccional\BoletoController;
use App\Models\Viaje;

Route::get('/', function () {
     $viajesProximos = Viaje::with('ruta')
          ->reservables()
          ->withCount(['asientos as asientos_disponibles' => function ($query) {
               $query->activos()->where('estado', 'disponible');
          }])
          ->orderBy('fecha_viaje')
          ->orderBy('hora_salida')
          ->take(6)
          ->get();

     return view('welcome', compact('viajesProximos'));
});

Route::get('/buscar-viajes', function () {
     $cantidad = max(1, (int) request('pasajeros', 1));

     $viajesProximos = Viaje::with('ruta')
          ->reservables()
          ->withCount(['asientos as asientos_disponibles' => function ($query) {
               $query->activos()->where('estado', 'disponible');
          }])
          ->when(request('origen'), function ($query, $origen) {
               $query->whereHas('ruta', fn ($ruta) => $ruta->where('origen', 'like', '%' . trim($origen) . '%'));
          })
          ->when(request('destino'), function ($query, $destino) {
               $query->whereHas('ruta', fn ($ruta) => $ruta->where('destino', 'like', '%' . trim($destino) . '%'));
          })
          ->when(request('fecha'), fn ($query, $fecha) => $query->whereDate('fecha_viaje', $fecha))
          ->having('asientos_disponibles', '>=', $cantidad)
          ->orderBy('fecha_viaje')
          ->orderBy('hora_salida')
          ->get();

     return view('welcome', compact('viajesProximos'));
})->name('viajes.buscar');

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

    Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario'])->except(['show']);
    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol'])->except(['show']);
    Route::resource('usuariorol', UsuarioRolController::class)->parameters(['usuariorol' => 'usuario'])->except(['show']);
});

// ── MÓDULO PARAMETRIZACIÓN ───────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('parametrizacion')->name('parametrizacion.')->group(function () {

    Route::resource('clientes', ClienteController::class)->parameters(['clientes' => 'cliente'])->except(['show']);
    Route::resource('buses', BusController::class)->parameters(['buses' => 'bus'])->except(['show']);
    Route::resource('conductores', ConductorController::class)->parameters(['conductores' => 'conductor'])->except(['show']);
    Route::resource('rutas', RutaController::class)->parameters(['rutas' => 'ruta'])->except(['show']);
    Route::resource('tipoencomiendas', TipoEncomiendaController::class)->parameters(['tipoencomiendas' => 'tipoEncomienda'])->except(['show']);
    Route::resource('tipoincidencias', TipoIncidenciaController::class)->parameters(['tipoincidencias' => 'tipoIncidencia'])->except(['show']);
    Route::get('asientoviaje/{asientoviaje}/imprimir', [AsientoViajeController::class, 'imprimir'])
         ->name('asientoviaje.imprimir');
    Route::resource('asientoviaje', AsientoViajeController::class)
         ->parameters(['asientoviaje' => 'asientoviaje'])
         ->only(['index', 'show', 'edit', 'update']);
});

Route::middleware(['auth', 'verified'])->prefix('transaccional')->name('transaccional.')->group(function () {

    Route::resource('viajes', ViajeController::class)->parameters(['viajes' => 'viaje'])->except(['show']);
    Route::resource('asignacionconductor', AsignacionConductorController::class)->parameters(['asignacionconductor' => 'viaje'])->except(['show']);
    Route::resource('incidenciaviaje', IncidenciaViajeController::class)->parameters(['incidenciaviaje' => 'incidenciaviaje'])->except(['show']);
    Route::resource('encomiendas', EncomiendaController::class)->parameters(['encomiendas' => 'encomienda'])->except(['show']);
    Route::resource('notificaciones', NotificacionController::class)->parameters(['notificaciones' => 'notificacion'])->except(['show']);
    Route::resource('reservas', ReservaController::class)->parameters(['reservas' => 'reserva'])->except(['show']);

    // ── RUTAS DE BOLETOS (Peticiones GET y de acción) ───────────────
    Route::get('boletos/acceso-directo', [BoletoController::class, 'index'])
         ->name('boletos.acceso-directo');

    Route::get('boletos/asientos/{viaje}', [BoletoController::class, 'asientosDisponibles'])
         ->name('boletos.asientos-disponibles');

    Route::get('boletos/viaje/{viaje}', [BoletoController::class, 'porViaje'])
         ->name('boletos.por-viaje');

    Route::patch('boletos/{boleto}/confirmar-pago', [BoletoController::class, 'confirmarPago'])
         ->name('boletos.confirmar-pago');

    // ── RESOURCE DE BOLETOS (Irá después de las rutas GET) ──────────
    Route::resource('boletos', BoletoController::class)->parameters(['boletos' => 'boleto'])->except(['show']);

    Route::get('encomiendas/{encomienda}/imprimir', [EncomiendaController::class, 'imprimir'])
         ->name('encomiendas.imprimir');
});

Route::get('transaccional/boletos/{boleto}/imprimir', [BoletoController::class, 'imprimir'])->name('transaccional.boletos.imprimir');

Route::middleware('guest:cliente')->name('cliente.')->group(function () {
    Route::get('cliente/register', [ClienteAuthController::class, 'showRegistrationForm'])
         ->name('register');
    Route::post('cliente/register', [ClienteAuthController::class, 'register'])
         ->name('register.store');
    Route::get('cliente/login', [ClienteAuthController::class, 'showLoginForm'])
         ->name('login');
    Route::post('cliente/login', [ClienteAuthController::class, 'login'])
         ->name('login.store');
});

Route::middleware('auth:cliente')->name('cliente.')->group(function () {
    Route::get('cliente/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');
    Route::resource('cliente/reservas', ClienteReservaController::class)
         ->only(['index', 'create', 'store']);
    Route::resource('cliente/boletos', ClienteBoletoController::class)
         ->only(['index', 'store']);
    Route::get('cliente/boletos/asientos/{viaje}', [ClienteBoletoController::class, 'asientosDisponibles'])
         ->name('boletos.asientos-disponibles');
    Route::get('cliente/boletos/create/{reserva}', [ClienteBoletoController::class, 'create'])
         ->name('boletos.create');
    Route::get('cliente/boletos/qr/{reserva}', [ClienteBoletoController::class, 'qr'])
         ->name('boletos.qr');
    Route::post('cliente/boletos/{reserva}/confirmar-pago', [ClienteBoletoController::class, 'confirmarPago'])
         ->name('boletos.confirmar-pago');
    Route::get('cliente/encomiendas', [ClienteEncomiendaController::class, 'index'])
         ->name('encomiendas.index');
    Route::get('cliente/notificaciones', [ClienteNotificacionController::class, 'index'])
         ->name('notificaciones.index');
    Route::post('cliente/logout', [ClienteAuthController::class, 'logout'])
         ->name('logout');
});

require __DIR__.'/auth.php';
