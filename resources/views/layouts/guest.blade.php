<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0
                    bg-gradient-to-br from-slate-800 via-slate-900 to-gray-900">

            {{-- Logo / Marca --}}
            <div class="mb-4 text-center">
                <a href="/" class="inline-flex items-center gap-2">
                    <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">P</span>
                    </div>
                    <span class="text-white text-2xl font-bold tracking-tight">MiValle</span>
                </a>
                <p class="text-slate-400 text-sm mt-1">Sistema de Gestión de Transporte</p>
            </div>

            {{-- Card del formulario --}}
            <div class="w-full sm:max-w-md mt-2 px-8 py-8 bg-white shadow-xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="text-slate-500 text-xs mt-6">
                &copy; {{ date('Y') }} MiValle. Todos los derechos reservados.
            </p>
        </div>
    </body>
</html>