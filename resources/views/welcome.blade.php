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
    <body class="font-sans antialiased bg-slate-950 text-slate-100">
        <div class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(168,85,247,0.16),_transparent_20%),linear-gradient(180deg,_#020617,_#0b1221)]">
            <header class="border-b border-white/10 bg-slate-950/90 backdrop-blur-xl">
                <div class="page-content-shell flex flex-col gap-4 py-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-gradient-to-br from-sky-500 to-violet-600 shadow-lg shadow-sky-500/20">
                            <span class="text-xl font-semibold text-white">P</span>
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-[0.32em] text-slate-400">PValle</p>
                            <h1 class="text-xl font-semibold text-white">Tu reserva y viaje en una sola app</h1>
                        </div>
                    </div>

                    <nav class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-secondary">Regístrate ahora</a>
                        @endif
                    </nav>
                </div>
            </header>

            <main class="page-content-shell py-16">
                <div class="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <section class="space-y-8 lg:max-w-2xl">
                        <span class="inline-flex rounded-full border border-slate-700 bg-slate-900/80 px-4 py-1 text-xs uppercase tracking-[0.32em] text-slate-300">Reserva tu viaje desde Santa Cruz</span>
                        <div class="space-y-6">
                            <h2 class="text-4xl font-semibold tracking-tight text-white sm:text-5xl">Viaja seguro y consulta tu encomienda en tiempo real.</h2>
                            <p class="text-lg leading-8 text-slate-300">En PValle puedes reservar tu pasaje desde Santa Cruz hacia Cochabamba, Sucre, Tarija y otros destinos cercanos. Todo en una experiencia simple para el pasajero.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-6 shadow-[0_24px_80px_-40px_rgba(15,23,42,0.7)]">
                                <p class="text-sm uppercase tracking-[0.32em] text-sky-300">Fácil</p>
                                <h3 class="mt-3 text-xl font-semibold text-white">Sube tu pasaje rápido</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Selecciona tu destino, tu asiento y confirma con el método que prefieras.</p>
                            </div>
                            <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-6 shadow-[0_24px_80px_-40px_rgba(15,23,42,0.7)]">
                                <p class="text-sm uppercase tracking-[0.32em] text-violet-300">Transparente</p>
                                <h3 class="mt-3 text-xl font-semibold text-white">Tu encomienda en línea</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Sigue tu paquete hasta el destino y recibe avisos cuando esté listo para recoger.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('register') }}" class="btn btn-primary w-full sm:w-auto">Regístrate ahora</a>
                            <a href="{{ route('login') }}" class="btn btn-secondary w-full sm:w-auto">Inicia sesión</a>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-5 text-center">
                                <p class="text-sm uppercase tracking-[0.32em] text-slate-400">1</p>
                                <p class="mt-4 text-base font-semibold text-white">Elige tu fecha</p>
                            </div>
                            <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-5 text-center">
                                <p class="text-sm uppercase tracking-[0.32em] text-slate-400">2</p>
                                <p class="mt-4 text-base font-semibold text-white">Compra tu boleto</p>
                            </div>
                            <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-5 text-center">
                                <p class="text-sm uppercase tracking-[0.32em] text-slate-400">3</p>
                                <p class="mt-4 text-base font-semibold text-white">Revisa tu viaje</p>
                            </div>
                        </div>
                    </section>

                    <section class="relative">
                        <div class="absolute -right-12 top-10 h-52 w-52 rounded-full bg-sky-500/10 blur-3xl"></div>
                        <div class="absolute -left-10 bottom-10 h-40 w-40 rounded-full bg-violet-500/10 blur-3xl"></div>
                        <div class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-slate-900/90 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur-xl">
                            <div class="relative overflow-hidden rounded-[2rem] bg-slate-950/90 p-4">
                                <img src="https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80" alt="Pasajeros abordando un bus moderno" class="h-72 w-full rounded-[1.75rem] object-cover object-center shadow-lg shadow-slate-950/40" loading="lazy" decoding="async">
                                <div class="absolute inset-x-0 bottom-0 rounded-b-[1.75rem] bg-gradient-to-t from-slate-950/95 to-transparent px-4 py-4 text-slate-100">
                                    <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Viaje destacado</p>
                                    <p class="mt-2 text-lg font-semibold">Santa Cruz → Cochabamba</p>
                                    <p class="text-sm text-slate-400">Salida 10:00 | Terminal A</p>
                                </div>
                            </div>

                            <div class="mt-6 rounded-[2rem] border border-slate-800 bg-slate-950/90 p-6 shadow-inner shadow-slate-950/40">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Estado del viaje</p>
                                        <p class="mt-2 text-lg font-semibold text-white">Confirmado</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300">A tiempo</span>
                                </div>

                                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-3xl bg-slate-950/80 p-4">
                                        <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Asiento</p>
                                        <p class="mt-2 text-lg font-semibold text-white">B12</p>
                                        <p class="text-slate-500">Coche 4</p>
                                    </div>
                                    <div class="rounded-3xl bg-slate-950/80 p-4">
                                        <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Encomienda</p>
                                        <p class="mt-2 text-lg font-semibold text-white">KG 12</p>
                                        <p class="text-slate-500">Listo para entrega</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="mt-16 rounded-[2rem] border border-white/10 bg-slate-900/80 px-6 py-10 shadow-2xl shadow-slate-950/40 backdrop-blur-xl">
                    <div class="grid gap-8 lg:grid-cols-3">
                        <div class="space-y-4">
                            <p class="text-sm uppercase tracking-[0.32em] text-slate-400">Ideal para clientes</p>
                            <h3 class="text-3xl font-semibold text-white">Todo tu viaje en una sola pantalla</h3>
                            <p class="text-slate-400">Reserva, revisa tu asiento y sigue tus envíos desde tu cuenta de cliente. Especial para pasajeros que viajan desde Santa Cruz.</p>
                        </div>

                        <div class="space-y-4 rounded-[2rem] border border-slate-800 bg-slate-950/80 p-6">
                            <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Destinos</p>
                            <ul class="space-y-3 text-slate-300">
                                <li class="flex items-start gap-2"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-500/20 text-sky-300">✓</span>Santa Cruz → Cochabamba</li>
                                <li class="flex items-start gap-2"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-500/20 text-sky-300">✓</span>Santa Cruz → Sucre</li>
                                <li class="flex items-start gap-2"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-500/20 text-sky-300">✓</span>Santa Cruz → Tarija</li>
                            </ul>
                        </div>

                        <div class="space-y-4 rounded-[2rem] border border-slate-800 bg-slate-950/80 p-6">
                            <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Ventajas</p>
                            <ul class="space-y-3 text-slate-300">
                                <li>• Compra rápida y confirmación al instante.</li>
                                <li>• Notificaciones de viaje vía SMS o correo.</li>
                                <li>• Consulta tus encomiendas y horarios desde el perfil.</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="page-content-shell py-8 text-center text-slate-500">
                <p>&copy; {{ date('Y') }} PValle. Todos los derechos reservados.</p>
            </footer>
        </div>
    </body>
</html>