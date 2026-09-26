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
                        @if (auth()->check() && auth()->user()->hasRole('Recepcionista'))
                            {{-- Estado de la señal enviada por esta sesión de recepción, no del internet global. --}}
                            <div id="reception-heartbeat-status" class="mb-4 text-right text-xs text-slate-500" role="status" aria-live="polite">
                                Verificando conexión con recepción...
                            </div>
                        @endif
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        @if (auth()->check() && auth()->user()->hasRole('Recepcionista'))
            <script>
                (() => {
                    const status = document.getElementById('reception-heartbeat-status');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const sendHeartbeat = async () => {
                        try {
                            const response = await fetch(@json(route('recepcion.presencia')), {
                                method: 'POST',
                                credentials: 'same-origin',
                                cache: 'no-store',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                            });
                            const data = await response.json();

                            if (!response.ok || data.ok !== true) throw new Error('Heartbeat rejected');
                            status.textContent = 'Tu conexión con recepción está activa.';
                            status.className = 'mb-4 text-right text-xs text-emerald-700';
                        } catch {
                            status.textContent = 'No se pudo confirmar la conexión. Las reservas en línea podrían pausarse.';
                            status.className = 'mb-4 text-right text-xs text-amber-700';
                        }
                    };

                    sendHeartbeat();
                    window.setInterval(sendHeartbeat, 15000);
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) sendHeartbeat();
                    });
                })();
            </script>
        @endif
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
