<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'PValle') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-800 via-slate-900 to-gray-900 min-h-screen flex items-center justify-center">

        <div class="text-center px-6">

            {{-- Logo --}}
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-2xl">P</span>
                </div>
                <span class="text-white text-3xl font-bold tracking-tight">PValle</span>
            </div>

            <p class="text-slate-400 mb-8">Sistema de Gestión de Transporte</p>

            <div class="flex items-center justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                        Ir al Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                        Iniciar Sesión
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="border border-slate-600 text-slate-300 hover:bg-slate-800 px-6 py-2.5 rounded-lg text-sm font-medium transition">
                            Registrarse
                        </a>
                    @endif
                @endauth
            </div>

            <p class="text-slate-500 text-xs mt-10">
                &copy; {{ date('Y') }} PValle. Todos los derechos reservados.
            </p>
        </div>
    </body>
</html>