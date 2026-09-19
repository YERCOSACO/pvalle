<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Trans Comarapa') }} | Viajes Santa Cruz - Comarapa</title>

        <meta name="description" content="Compra tus pasajes de bus Santa Cruz - Comarapa con Trans Comarapa. Reserva en línea, elige tu asiento y viaja seguro.">
        <meta property="og:title" content="Trans Comarapa | Viajes Santa Cruz - Comarapa">
        <meta property="og:description" content="Reserva pasajes de bus Santa Cruz - Comarapa en línea. Horarios, asientos disponibles y pago seguro.">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('images/og-trans-comarapa.jpg') }}">
        <meta name="theme-color" content="#0B2A1E">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700,600i|figtree:400,500,600,700|plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root{
                --mv-bosque-900:#0B2A1E;
                --mv-bosque-800:#0F3626;
                --mv-selva-600:#1B6B45;
                --mv-selva-500:#238453;
                --mv-brote-400:#6FCF64;
                --mv-brote-300:#9BE28C;
                --mv-sol-400:#F2C94C;
                --mv-piedra-50:#F6F3E9;
                --mv-piedra-100:#EEE9D8;
                --mv-tinta-900:#12241C;
                --mv-blanco:#FBFAF5;
            }
            [x-cloak]{ display:none !important; }
            .font-display{ font-family:'Fraunces', ui-serif, Georgia, serif; }
            .font-eyebrow{ font-family:'Figtree', ui-sans-serif, sans-serif; }
            body{ font-family:'Plus Jakarta Sans', ui-sans-serif, sans-serif; color:var(--mv-tinta-900); background:var(--mv-piedra-50); }

            /* Cresta de montañas — elemento firma, hace eco del logo */
            .mv-cresta{
                position:absolute; left:0; right:0; bottom:-1px; height:120px;
                background-repeat:no-repeat; background-size:cover; background-position:bottom;
            }
            .mv-cresta svg{ width:100%; height:100%; display:block; }
            .mv-cresta-watermark{
                position:absolute; inset:0; opacity:.06; pointer-events:none;
            }

            .mv-btn-primary{
                background:var(--mv-brote-400); color:var(--mv-bosque-900);
                box-shadow:0 10px 30px -12px rgba(111,207,100,.55);
            }
            .mv-btn-primary:hover{ background:var(--mv-brote-300); }
            .mv-btn-dark{
                background:var(--mv-bosque-900); color:var(--mv-blanco);
            }
            .mv-btn-dark:hover{ background:var(--mv-selva-600); }
            .mv-btn-ghost{
                background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.25); color:#fff;
            }
            .mv-btn-ghost:hover{ background:rgba(255,255,255,.18); }

            .mv-card{
                background:var(--mv-blanco);
                border:1px solid rgba(15,54,38,.08);
            }
            .mv-card:hover{ border-color:rgba(27,107,69,.35); }

            .mv-pill{
                background:rgba(27,107,69,.10); color:var(--mv-selva-600);
            }
            .mv-pill-sol{
                background:rgba(242,201,76,.18); color:#8a6d16;
            }

            .mv-input{
                background:var(--mv-piedra-50);
                border:1px solid rgba(15,54,38,.14);
            }
            .mv-input:focus{
                outline:none; border-color:var(--mv-selva-500);
                box-shadow:0 0 0 3px rgba(35,132,83,.18);
            }
        </style>
    </head>
    <body class="landing-page antialiased">
        @php
            $heroImage = file_exists(public_path('images/hero-comarapa.jpg'))
                ? asset('images/hero-comarapa.jpg')
                : asset('images/logo.png');

            $destinos = [
                ['Comarapa', 'Pueblo cabecera de ruta: ferias, quesos y gastronomía local', file_exists(public_path('images/destinos/comarapa.jpg')) ? 'images/destinos/comarapa.jpg' : 'images/logo.png'],
                ['Laguna Verde', 'Aguas de tono esmeralda en la zona alta de las montañas', file_exists(public_path('images/destinos/laguna-verde.jpg')) ? 'images/destinos/laguna-verde.jpg' : 'images/logo.png'],
                ['Jardín de las Cactáceas', 'Más de 50 especies de cactus, único reservorio así en Bolivia', file_exists(public_path('images/destinos/cactaceas.jpg')) ? 'images/destinos/cactaceas.jpg' : 'images/logo.png'],
                ['Represa La Cañada', 'Pesca y recreación a 5 km del pueblo', file_exists(public_path('images/destinos/la-canada.jpg')) ? 'images/destinos/la-canada.jpg' : 'images/logo.png'],
            ];
        @endphp
        <div class="min-h-screen">
            <header x-data="{ mobileOpen: false }" class="border-b border-black/5 bg-[var(--mv-blanco)]/90 backdrop-blur-xl sticky top-0 z-30">
                <div class="page-content-shell flex min-h-20 items-center justify-between gap-5">
                    <x-brand-logo href="{{ url('/') }}" compact />

                    <nav aria-label="Navegación principal" class="hidden items-center gap-7 text-sm font-semibold text-[var(--mv-tinta-900)]/70 md:flex">
                        <a href="#viajes" class="transition hover:text-[var(--mv-selva-600)]">Viajes</a>
                        <a href="#destinos" class="transition hover:text-[var(--mv-selva-600)]">Destinos</a>
                        <a href="#como-funciona" class="transition hover:text-[var(--mv-selva-600)]">Cómo funciona</a>
                    </nav>

                    <div class="hidden items-center gap-2 md:flex">
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-semibold text-[var(--mv-tinta-900)]/70 hover:text-[var(--mv-tinta-900)]">Ingresar</a>
                        <a href="{{ route('cliente.register') }}" class="mv-btn-primary rounded-xl px-4 py-2.5 text-sm font-bold transition">Registrarse</a>
                    </div>

                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen"
                        aria-controls="mobile-menu"
                        aria-label="Abrir menú"
                        class="flex h-11 w-11 items-center justify-center rounded-lg text-[var(--mv-tinta-900)] transition hover:bg-black/5 md:hidden"
                    >
                        <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div
                    id="mobile-menu"
                    x-show="mobileOpen"
                    x-cloak
                    x-transition
                    @click.outside="mobileOpen = false"
                    @keydown.escape.window="mobileOpen = false"
                    class="border-t border-black/5 bg-[var(--mv-blanco)] md:hidden"
                >
                    <nav aria-label="Menú móvil" class="page-content-shell flex flex-col gap-1 py-3 text-sm font-semibold text-[var(--mv-tinta-900)]/80">
                        <a href="#viajes" @click="mobileOpen = false" class="rounded-lg px-3 py-2.5 hover:bg-black/5">Viajes</a>
                        <a href="#destinos" @click="mobileOpen = false" class="rounded-lg px-3 py-2.5 hover:bg-black/5">Destinos</a>
                        <a href="#como-funciona" @click="mobileOpen = false" class="rounded-lg px-3 py-2.5 hover:bg-black/5">Cómo funciona</a>
                        <div class="mt-2 flex flex-col gap-2 border-t border-black/5 pt-3">
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-center hover:bg-black/5">Ingresar</a>
                            <a href="{{ route('cliente.register') }}" class="mv-btn-primary rounded-xl px-4 py-2.5 text-center font-bold">Registrarse</a>
                        </div>
                    </nav>
                </div>
            </header>

            <main>
                <section class="relative isolate overflow-hidden bg-[var(--mv-bosque-900)] pb-28">
                    <img src="{{ $heroImage }}" alt="Bus de Trans Comarapa en la ruta Santa Cruz - Comarapa" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-20" loading="eager" decoding="async">
                    <div class="absolute inset-0 -z-10 bg-[linear-gradient(115deg,_#0B2A1E_15%,_rgba(15,54,38,0.94)_55%,_rgba(35,132,83,0.45))]"></div>

                    <div class="mx-auto grid max-w-7xl gap-10 px-5 pt-16 sm:px-8 sm:pt-24 lg:grid-cols-[0.95fr_1.05fr] lg:items-center lg:px-10">
                        <div class="max-w-xl text-white">
                            <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.28em] text-[var(--mv-brote-300)]">Tu próximo viaje empieza aquí</p>
                            <h1 class="font-display mt-5 text-4xl font-semibold leading-[1.05] text-white sm:text-6xl">
                                Viaja seguro<br>a Comarapa.
                            </h1>
                            <p class="mt-5 max-w-md text-base leading-7 text-slate-200/85 sm:text-lg">
                                Encuentra pasajes, organiza tu ruta y mantén toda la información de tu viaje en un solo lugar.
                            </p>
                            <div class="mt-7 flex flex-wrap gap-3">
                                <a href="#buscador" class="mv-btn-primary rounded-xl px-5 py-3 text-sm font-bold transition">Buscar un viaje</a>
                                <a href="#viajes" class="mv-btn-ghost rounded-xl px-5 py-3 text-sm font-bold transition">Ver salidas</a>
                            </div>
                        </div>

                        <form id="buscador" class="mv-card rounded-[2rem] p-5 shadow-2xl shadow-black/40 sm:p-7" action="{{ route('viajes.buscar') }}" method="GET">
                            <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.2em] text-[var(--mv-selva-600)]">Reserva tu pasaje</p>
                            <h2 class="font-display mt-2 text-2xl font-semibold text-[var(--mv-tinta-900)]">¿A dónde quieres ir?</h2>
                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <label class="text-sm font-semibold text-[var(--mv-tinta-900)]/80">Desde
                                    <select name="origen" class="mv-input mt-2 w-full rounded-lg px-3 py-2.5 text-sm">
                                        <option value="">Ciudad de origen</option>
                                        @foreach($ciudades ?? ['Santa Cruz de la Sierra', 'Comarapa'] as $ciudad)
                                            <option value="{{ $ciudad }}" @selected(request('origen') === $ciudad)>{{ $ciudad }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-sm font-semibold text-[var(--mv-tinta-900)]/80">Hacia
                                    <select name="destino" class="mv-input mt-2 w-full rounded-lg px-3 py-2.5 text-sm">
                                        <option value="">Ciudad de destino</option>
                                        @foreach($ciudades ?? ['Comarapa', 'Santa Cruz de la Sierra'] as $ciudad)
                                            <option value="{{ $ciudad }}" @selected(request('destino') === $ciudad)>{{ $ciudad }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-sm font-semibold text-[var(--mv-tinta-900)]/80">Fecha de salida
                                    <input name="fecha" type="date" value="{{ request('fecha') }}" min="{{ now()->toDateString() }}" class="mv-input mt-2 w-full rounded-lg px-3 py-2.5 text-sm">
                                </label>
                                <label class="text-sm font-semibold text-[var(--mv-tinta-900)]/80">Pasajeros
                                    <input name="pasajeros" type="number" min="1" max="45" step="1" value="{{ request('pasajeros', 1) }}" class="mv-input mt-2 w-full rounded-lg px-3 py-2.5 text-sm">
                                </label>
                            </div>
                            <button type="submit" class="mv-btn-dark mt-5 w-full rounded-xl py-3 text-sm font-bold transition">
                                Buscar pasajes <span aria-hidden="true">→</span>
                            </button>
                        </form>
                    </div>

                    <!-- Cresta de montañas: firma visual que replica el logo -->
                    <div class="mv-cresta">
                        <svg viewBox="0 0 1440 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 160 L0 90 L160 40 L260 80 L380 20 L520 90 L640 55 L760 100 L900 45 L1040 95 L1180 60 L1300 100 L1440 70 L1440 160 Z" fill="#EEE9D8" opacity="0.35"/>
                            <path d="M0 160 L0 110 L180 65 L320 105 L460 55 L600 110 L760 75 L920 115 L1080 70 L1240 112 L1440 90 L1440 160 Z" fill="#F6F3E9" opacity="0.7"/>
                            <path d="M0 160 L0 135 L200 108 L380 140 L560 100 L740 138 L920 105 L1120 140 L1440 118 L1440 160 Z" fill="#F6F3E9"/>
                        </svg>
                    </div>
                </section>

                <section id="viajes" class="page-content-shell -mt-14 pb-16 pt-6 relative z-10">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.22em] text-[var(--mv-selva-600)]">Salidas disponibles</p>
                            <h2 class="font-display mt-2 text-3xl font-semibold text-[var(--mv-tinta-900)] sm:text-4xl">Viajes que puedes reservar</h2>
                            <p class="mt-2 text-sm text-[var(--mv-tinta-900)]/60">Solo mostramos viajes futuros con asientos disponibles.</p>
                            @if(request()->hasAny(['origen', 'destino']))
                                <p class="mt-2 text-sm font-semibold text-[var(--mv-selva-600)]">
                                    Resultados: {{ request('origen') ?: 'cualquier origen' }} → {{ request('destino') ?: 'cualquier destino' }}
                                </p>
                            @endif
                        </div>
                        <a href="#buscador" class="text-sm font-semibold text-[var(--mv-selva-600)] hover:text-[var(--mv-bosque-900)]">Nueva búsqueda →</a>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @forelse($viajesProximos as $viaje)
                            <article class="mv-card rounded-2xl p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--mv-tinta-900)]/40">{{ optional($viaje->fecha_viaje)->format('d/m/Y') }}</p>
                                        <h3 class="font-display mt-2 text-lg font-semibold text-[var(--mv-tinta-900)]">{{ optional($viaje->ruta)->nombre_ruta ?? 'Ruta disponible' }}</h3>
                                    </div>
                                    <span class="mv-pill status-pill rounded-full px-3 py-1 text-xs font-bold">{{ $viaje->hora_salida ? substr($viaje->hora_salida, 0, 5) : '--:--' }}</span>
                                </div>
                                <div class="mt-5 flex items-center justify-between border-t border-black/5 pt-4 text-sm text-[var(--mv-tinta-900)]/60">
                                    <span>{{ $viaje->asientos_disponibles }} cupo(s) disponible(s)</span>
                                    <a href="{{ route('cliente.reservas.create', ['viaje_id' => $viaje->id, 'pasajeros' => request('pasajeros', 1)]) }}" class="font-bold text-[var(--mv-selva-600)]">Seleccionar →</a>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full empty-state rounded-2xl border border-dashed border-[var(--mv-selva-600)]/25 bg-white/60 p-8 text-center text-[var(--mv-tinta-900)]/60">
                                No hay viajes disponibles para esta búsqueda.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="destinos" class="border-y border-black/5 bg-[var(--mv-piedra-100)]">
                    <div class="page-content-shell py-16">
                        <div class="max-w-xl">
                            <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.22em] text-[var(--mv-selva-600)]">Inspiración para tu ruta</p>
                            <h2 class="font-display mt-2 text-3xl font-semibold text-[var(--mv-tinta-900)] sm:text-4xl">Lo que puedes conocer en Comarapa.</h2>
                        </div>
                        {{--
                            Destinos reales de la provincia Manuel María Caballero / Comarapa.
                            Reemplaza las rutas de imagen con fotos propias en public/images/destinos/.
                        --}}
                        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($destinos as $destino)
                                <a href="#viajes" class="group relative min-h-60 overflow-hidden rounded-2xl bg-[var(--mv-bosque-900)] p-5 text-white">
                                    <img src="{{ asset($destino[2]) }}" alt="{{ $destino[0] }}" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover opacity-60 transition duration-500 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#0B2A1E] via-[#0B2A1E]/50 to-transparent"></div>
                                    <span class="relative flex h-full flex-col justify-end">
                                        <strong class="font-display text-xl">{{ $destino[0] }}</strong>
                                        <small class="mt-1 text-slate-200/80">{{ $destino[1] }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="como-funciona" class="page-content-shell py-16">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                        <div>
                            <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.22em] text-[var(--mv-selva-600)]">Así de fácil</p>
                            <h2 class="font-display mt-2 text-3xl font-semibold text-[var(--mv-tinta-900)] sm:text-4xl">Tu viaje, sin vueltas.</h2>
                            <p class="mt-4 max-w-md leading-7 text-[var(--mv-tinta-900)]/65">Elige una salida, completa tus datos y recibe la información de tu reserva para viajar con tranquilidad.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach([['01', 'Busca', 'Indica origen, destino y fecha.'], ['02', 'Elige', 'Compara las mejores salidas.'], ['03', 'Viaja', 'Recibe tu boleto y disfruta.']] as $paso)
                                <div class="mv-card rounded-2xl p-5 shadow-sm">
                                    <b class="font-display text-2xl text-[var(--mv-selva-600)]">{{ $paso[0] }}</b>
                                    <h3 class="mt-8 font-semibold text-[var(--mv-tinta-900)]">{{ $paso[1] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-[var(--mv-tinta-900)]/60">{{ $paso[2] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </main>

            <footer class="relative overflow-hidden bg-[var(--mv-bosque-900)] text-slate-300">
                <div class="mv-cresta-watermark">
                    <svg viewBox="0 0 1440 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 160 L0 100 L220 50 L400 100 L600 40 L820 100 L1040 55 L1260 105 L1440 70 L1440 160 Z" fill="#FBFAF5"/>
                    </svg>
                </div>

                <div class="page-content-shell relative flex flex-col gap-8 py-10">
                    <div class="flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <strong class="font-display text-2xl text-white">Trans Comarapa</strong>
                            <p class="mt-1 text-sm text-slate-400">Viaja seguro de Santa Cruz a Comarapa.</p>
                        </div>

                        <div>
                            <p class="mb-4 text-sm font-semibold text-slate-400">Síguenos en nuestras redes</p>
                            <div class="flex items-center gap-5">
                                <a href="#" aria-label="Facebook" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/5 text-slate-300 transition-all duration-300 hover:-translate-y-1 hover:bg-[var(--mv-selva-500)] hover:text-white hover:shadow-lg hover:shadow-[var(--mv-selva-500)]/30">
                                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14 8h3V5h-3c-2.76 0-5 2.24-5 5v2H6v3h3v6h3v-6h3l1-3h-4v-2c0-1.1.9-2 2-2z"/></svg>
                                </a>
                                <a href="#" aria-label="WhatsApp" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/5 text-slate-300 transition-all duration-300 hover:-translate-y-1 hover:bg-[var(--mv-brote-400)] hover:text-[var(--mv-bosque-900)] hover:shadow-lg hover:shadow-[var(--mv-brote-400)]/30">
                                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M20.52 3.48A11.86 11.86 0 0 0 12.04 0C5.48 0 .14 5.34.14 11.9c0 2.1.55 4.15 1.6 5.95L0 24l6.3-1.65a11.9 11.9 0 0 0 5.74 1.47h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.17-3.43-8.44ZM12.05 21.8a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.23-.38a9.88 9.88 0 1 1 8.36 4.64Zm5.42-7.4c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.74-1.64-2.04-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.26.49 1.69.63.71.23 1.35.2 1.86.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/></svg>
                                </a>
                                <a href="#" aria-label="Instagram" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/5 text-slate-300 transition-all duration-300 hover:-translate-y-1 hover:bg-gradient-to-tr hover:from-[var(--mv-selva-500)] hover:to-[var(--mv-brote-400)] hover:text-white hover:shadow-lg">
                                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.25-3.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z"/></svg>
                                </a>
                                <a href="#" aria-label="TikTok" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/5 text-slate-300 transition-all duration-300 hover:-translate-y-1 hover:bg-black hover:text-white hover:shadow-lg hover:shadow-black/30">
                                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-3.77V2h-3.45v13.67a2.91 2.91 0 1 1-2-2.75V9.42a6.35 6.35 0 1 0 5.45 6.25V8.72a8.26 8.26 0 0 0 4.83 1.55V6.84a4.79 4.79 0 0 1-1.06-.15Z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @if(request()->hasAny(['origen', 'destino', 'fecha']))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.getElementById('viajes')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            </script>
        @endif
    </body>
</html>
