<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ClienteLoginRequest;
use App\Http\Requests\Auth\ClienteRegisterRequest;
use App\Models\Cliente;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClienteAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.cliente-login');
    }

    public function login(ClienteLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('cliente.dashboard', absolute: false));
    }

    public function showRegistrationForm(): View
    {
        return view('auth.cliente-register');
    }

    public function register(ClienteRegisterRequest $request): RedirectResponse
    {
        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'contrasena' => Hash::make($request->contrasena),
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'fecha_nacimiento' => $request->fecha_nacimiento,
        ]);

        event(new Registered($cliente));
        Auth::guard('cliente')->login($cliente);

        return redirect()->route('cliente.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cliente.login');
    }
}
