<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root{
                --mv-bosque-900:#0B2A1E; --mv-bosque-800:#0F3626;
                --mv-selva-600:#1B6B45; --mv-selva-500:#238453;
                --mv-brote-400:#6FCF64; --mv-brote-300:#9BE28C;
                --mv-piedra-50:#F6F3E9; --mv-tinta-900:#12241C; --mv-blanco:#FBFAF5;
            }
            .font-display{ font-family:'Fraunces', ui-serif, Georgia, serif; }
            .mv-input{ background:var(--mv-piedra-50); border:1px solid rgba(15,54,38,.14); color:var(--mv-tinta-900); }
            .mv-input:focus{ outline:none; border-color:var(--mv-selva-500); box-shadow:0 0 0 3px rgba(35,132,83,.18); }
            .mv-btn-primary{ background:var(--mv-brote-400); color:var(--mv-bosque-900); box-shadow:0 10px 30px -12px rgba(111,207,100,.55); }
            .mv-btn-primary:hover{ background:var(--mv-brote-300); }
            .mv-cresta{ position:absolute; left:0; right:0; bottom:-1px; height:110px; }
            .mv-cresta svg{ width:100%; height:100%; display:block; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative flex min-h-screen flex-col items-center overflow-hidden bg-[var(--mv-bosque-900)] pt-6 sm:justify-center sm:pt-0">

            <div class="absolute inset-0 -z-10 bg-[linear-gradient(115deg,_#0B2A1E_15%,_rgba(15,54,38,0.94)_55%,_rgba(35,132,83,0.4))]"></div>

            {{-- Logo / Marca --}}
            <div class="relative z-10 mb-4 text-center">
                <x-brand-logo href="{{ url('/') }}" dark />
                <p class="mt-1 text-sm text-[var(--mv-brote-300)]">Sistema de Gestión de Transporte</p>
            </div>

            {{-- Card del formulario --}}
            <div class="relative z-10 mt-2 w-full overflow-hidden rounded-[1.75rem] bg-[var(--mv-blanco)] px-8 py-8 shadow-2xl shadow-black/40 sm:max-w-md">
                {{ $slot }}
            </div>

            <div class="mv-cresta">
                <svg viewBox="0 0 1440 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 160 L0 110 L180 65 L320 105 L460 55 L600 110 L760 75 L920 115 L1080 70 L1240 112 L1440 90 L1440 160 Z" fill="#0F3626" opacity="0.7"/>
                    <path d="M0 160 L0 135 L200 108 L380 140 L560 100 L740 138 L920 105 L1120 140 L1440 118 L1440 160 Z" fill="#1B6B45" opacity="0.5"/>
                </svg>
            </div>
        </div>
    </body>
</html>