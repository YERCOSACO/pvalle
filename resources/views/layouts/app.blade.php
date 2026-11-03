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
    <body class="font-sans antialiased">
        <div class="app-shell lg:grid lg:min-h-screen lg:grid-cols-[18rem_minmax(0,1fr)]">
            <div class="hidden border-r border-slate-200 bg-slate-50 lg:block">
                <div class="sticky top-0 min-h-screen p-4">
                    <x-sidebar />
                </div>
            </div>

            <div class="flex min-h-screen flex-col">
                @include('layouts.navigation')

                @isset($header)
                    <header class="page-header-shell">
                        <div class="page-content-shell py-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    <div class="page-content-shell">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        {{-- Indicador visual de acceso a internet, compartido en las pantallas del personal. --}}
        <div data-network-status
             class="fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 shadow-lg"
             role="status"
             aria-live="polite"
             title="Se comprueba el acceso a internet. No identifica si la conexión es Wi-Fi o cable.">
            <span data-network-indicator class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
            <span data-network-label>Comprobando conexión...</span>
        </div>
    </body>
</html>
