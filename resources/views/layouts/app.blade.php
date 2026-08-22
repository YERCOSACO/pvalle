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
            <div class="hidden border-r border-white/10 bg-slate-950 lg:block">
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
    </body>
</html>
