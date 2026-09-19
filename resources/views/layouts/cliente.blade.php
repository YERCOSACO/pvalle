<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }} - Cliente</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-900">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_28%),linear-gradient(180deg,_#f8fafc_0%,_#eef2f7_100%)]">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 shadow-sm backdrop-blur">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex min-h-20 items-center justify-between gap-4">
                        <div class="min-w-0">
                            <x-brand-logo href="{{ route('cliente.dashboard') }}" size="header" />
                            <p class="hidden text-xs text-slate-500 sm:block">Viaja con información clara y seguimiento en un solo lugar.</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-xs text-slate-500">Sesión iniciada como</p>
                                <p class="max-w-40 truncate text-sm font-semibold text-slate-800">{{ auth('cliente')->user()->nombre_completo }}</p>
                            </div>
                            <form method="POST" action="{{ route('cliente.logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50">Salir</button>
                            </form>
                        </div>
                    </div>

                    <nav class="-mb-px flex gap-1 overflow-x-auto pb-2" aria-label="Navegación del cliente">
                        @foreach([
                            ['route' => 'cliente.dashboard', 'label' => 'Inicio', 'active' => 'cliente.dashboard'],
                            ['route' => 'cliente.reservas.index', 'label' => 'Reservas', 'active' => 'cliente.reservas.*'],
                            ['route' => 'cliente.boletos.index', 'label' => 'Boletos', 'active' => 'cliente.boletos.*'],
                            ['route' => 'cliente.encomiendas.index', 'label' => 'Encomiendas', 'active' => 'cliente.encomiendas.*'],
                            ['route' => 'cliente.notificaciones.index', 'label' => 'Avisos', 'active' => 'cliente.notificaciones.*'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold {{ request()->routeIs($item['active']) ? 'border-sky-600 text-sky-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('success') }}</div>
                @endif
                @if (session('info'))
                    <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800" role="status">{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        <p class="font-semibold">Revisa los datos ingresados</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
