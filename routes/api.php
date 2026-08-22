<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\ConductorApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/perfil', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('buses', BusApiController::class)->parameters(['buses' => 'bus']);
    Route::apiResource('clientes', ClienteApiController::class)->parameters(['clientes' => 'cliente']);
    Route::apiResource('conductores', ConductorApiController::class)->parameters(['conductores' => 'conductor']);
});
