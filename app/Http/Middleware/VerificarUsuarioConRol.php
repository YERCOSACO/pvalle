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

        if ($usuario && $usuario->roles->isEmpty()) {

            if (!$request->routeIs('profile.*') && !$request->routeIs('logout')) {
                return redirect()->route('profile.edit')
                    ->with('warning', 'Tu cuenta está pendiente de aprobación. Un administrador debe asignarte un rol para que puedas acceder al sistema.');
            }
        }

        return $next($request);
    }
}