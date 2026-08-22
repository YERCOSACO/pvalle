<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarUsuarioConRol
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = Auth::user();

        if ($usuario && method_exists($usuario, 'roles')) {
            $roles = $usuario->roles;
            if ($roles instanceof \Illuminate\Support\Collection && $roles->isEmpty()) {
                // Permite completar el flujo base de cuenta sin bloquear rutas
                // sensibles como verificación de correo, confirmación y cambio de contraseña.
                $rutaPermitida = $request->routeIs([
                    'profile.*',
                    'logout',
                    'verification.*',
                    'password.confirm',
                    'password.update',
                ]) || $request->is('confirm-password', 'password');

                if (! $rutaPermitida) {
                    return redirect()->route('profile.edit')
                        ->with('warning', 'Tu cuenta está pendiente de aprobación. Un administrador debe asignarte un rol para que puedas acceder al sistema.');
                }
            }
        }

        return $next($request);
    }
}
