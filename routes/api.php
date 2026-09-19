<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AsignacionConductorApiController;
use App\Http\Controllers\Api\AsientoViajeApiController;
use App\Http\Controllers\Api\BoletoApiController;
use App\Http\Controllers\Api\BusApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\ConductorApiController;
use App\Http\Controllers\Api\EncomiendaApiController;
use App\Http\Controllers\Api\IncidenciaViajeApiController;
use App\Http\Controllers\Api\NotificacionApiController;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\Api\RutaApiController;
use App\Http\Controllers\Api\TipoEncomiendaApiController;
use App\Http\Controllers\Api\TipoIncidenciaApiController;
use App\Http\Controllers\Api\ViajeApiController;
use Illuminate\Support\Facades\Route;



//7
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

    //2/////////////////////////////////////////////JWT//////7
Route::middleware('auth:api')->group(function () {
    Route::get('/perfil', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('buses', BusApiController::class)->parameters(['buses' => 'bus']);
    Route::apiResource('clientes', ClienteApiController::class)->parameters(['clientes' => 'cliente']);
    Route::apiResource('conductores', ConductorApiController::class)->parameters(['conductores' => 'conductor']);
    Route::apiResource('rutas', RutaApiController::class)->parameters(['rutas' => 'ruta']);
    Route::apiResource('tipoencomiendas', TipoEncomiendaApiController::class)->parameters(['tipoencomiendas' => 'tipoencomienda']);
    Route::apiResource('tipoincidencias', TipoIncidenciaApiController::class)->parameters(['tipoincidencias' => 'tipoincidencia']);
    Route::apiResource('viajes', ViajeApiController::class)->parameters(['viajes' => 'viaje']);
    Route::apiResource('reservas', ReservaApiController::class)->parameters(['reservas' => 'reserva']);
    Route::apiResource('boletos', BoletoApiController::class)->parameters(['boletos' => 'boleto']);
    Route::apiResource('encomiendas', EncomiendaApiController::class)->parameters(['encomiendas' => 'encomienda']);
    Route::apiResource('notificaciones', NotificacionApiController::class)->parameters(['notificaciones' => 'notificacion']);
    Route::apiResource('incidenciasviaje', IncidenciaViajeApiController::class)->parameters(['incidenciasviaje' => 'incidenciaviaje']);
    Route::apiResource('asientosviaje', AsientoViajeApiController::class)->parameters(['asientosviaje' => 'asientoviaje']);
    Route::apiResource('asignacionesconductor', AsignacionConductorApiController::class)->parameters(['asignacionesconductor' => 'asignacionconductor']);
});
