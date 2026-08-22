<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "PValle API",
    description: "Documentación de la API de PValle (Buses Mi Valle)"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Servidor local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}