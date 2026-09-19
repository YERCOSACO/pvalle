<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-700">Centro de control</p>
                <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Panel principal</h2>
                    
            </div>
        </div>
    </x-slot>

    @php
        $secciones = [
            [
                'nombre' => 'SEGURIDAD',
                'descripcion' => 'Administrar usuario,rol y permiso.',
                'items' => [
                    ['nombre' => 'ADMINISTRAR USUARIO', 'ruta' => 'seguridad.usuarios.index', 'permiso' => 'usuarios.ver'],
                    ['nombre' => 'ADMINISTRAR ROL', 'ruta' => 'seguridad.roles.index', 'permiso' => 'roles.ver'],
                    ['nombre' => 'ADMINISTRAR USUARIO_ROL', 'ruta' => 'seguridad.usuariorol.index', 'permiso' => 'usuariorol.ver'],
                ],
            ],
            [
                'nombre' => 'PARAMETRIZACIÓN',
                'descripcion' => 'Administra los datos base del servicio.',
                'items' => [
                    ['nombre' => 'ADMINISTRAR CLIENTE', 'ruta' => 'parametrizacion.clientes.index', 'permiso' => 'clientes.ver'],
                    ['nombre' => 'ADMINISTRAR BUS', 'ruta' => 'parametrizacion.buses.index', 'permiso' => 'buses.ver'],
                    ['nombre' => 'ADMINISTRAR CONDUCTOR', 'ruta' => 'parametrizacion.conductores.index', 'permiso' => 'conductores.ver'],
                    ['nombre' => 'ADMINISTRAR RUTA', 'ruta' => 'parametrizacion.rutas.index', 'permiso' => 'rutas.ver'],
                    ['nombre' => 'ADMINISTRAR TIPO_ENCOMIENDA', 'ruta' => 'parametrizacion.tipoencomiendas.index', 'permiso' => 'tipoencomiendas.ver'],
                    ['nombre' => 'ADMINISTRAR TIPO_INCIDENCIA', 'ruta' => 'parametrizacion.tipoincidencias.index', 'permiso' => 'tipoincidencias.ver'],
                    ['nombre' => 'ADMINISTRAR NOTIFICACION', 'ruta' => 'transaccional.notificaciones.index', 'permiso' => 'notificaciones.ver'],
                    ],
            ],
            [
                'nombre' => 'TRANSACCIONAL',
                'descripcion' => 'Opera Reservas, viajes, Encomiendas y mas.',
                'items' => [
                    ['nombre' => 'ADMINISTRAR RESERVA', 'ruta' => 'transaccional.reservas.index', 'permiso' => 'reservas.ver'],
                    ['nombre' => 'ADMINISTRAR BOLETO', 'ruta' => 'transaccional.boletos.index', 'permiso' => 'boletos.ver'],
                    ['nombre' => 'ADMINISTRAR VIAJE', 'ruta' => 'transaccional.viajes.index', 'permiso' => 'viajes.ver'],
                    ['nombre' => 'ADMINISTRAR ASIGNACIÓN_CONDUCTOR', 'ruta' => 'transaccional.asignacionconductor.index', 'permiso' => 'asignacionconductor.ver'],
                    ['nombre' => 'ADMINISTRAR INCIDENCIA', 'ruta' => 'transaccional.incidenciaviaje.index', 'permiso' => 'incidenciaviaje.ver'],
                    ['nombre' => 'ADMINISTRAR ENCOMIENDA', 'ruta' => 'transaccional.encomiendas.index', 'permiso' => 'encomiendas.ver'],
                ],
            ],
            [
                'nombre' => 'REPORTES',
                'descripcion' => 'Realiza reportes de viajes,asientos y mas.',
                'items' => [
                    ['nombre' => 'REPORTE ASIENTO_VIAJE', 'ruta' => 'parametrizacion.asientoviaje.index', 'permiso' => 'asientoviaje.ver'],
                ],
            ],
        ];
    @endphp

    <div class="space-y-8 py-8">
        <section class="dashboard-hero">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-100">Operaciones del sistema</p>
                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Bienvenido: {{ auth()->user()->name }}</h1>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            @foreach($secciones as $seccion)
                @php($itemsVisibles = collect($seccion['items'])->filter(fn ($item) => auth()->user()->can($item['permiso'])) )
                @if($itemsVisibles->isNotEmpty())
                    <section class="dashboard-module-card">
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-xl font-extrabold text-slate-950">{{ $seccion['nombre'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $seccion['descripcion'] }}</p>
                        </div>
                        <div class="mt-4 grid gap-2">
                            @foreach($itemsVisibles as $item)
                                <a href="{{ route($item['ruta']) }}" class="module-link group">
                                    <span class="flex items-center gap-3"><span class="module-link-dot"></span>{{ $item['nombre'] }}</span>
                                    <span class="text-lg text-slate-300 transition group-hover:translate-x-1 group-hover:text-sky-500" aria-hidden="true">→</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
