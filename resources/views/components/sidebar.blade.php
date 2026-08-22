@php
    $navigationGroups = [
        [
            'label' => 'Seguridad',
            'pattern' => 'seguridad.*',
            'items' => [
                ['label' => 'Usuario', 'route' => 'seguridad.usuarios.index', 'active' => 'seguridad.usuarios.*'],
                ['label' => 'Rol', 'route' => 'seguridad.roles.index', 'active' => 'seguridad.roles.*'],
                ['label' => 'Usuario Rol', 'route' => 'seguridad.usuariorol.index', 'active' => 'seguridad.usuariorol.*'],
            ],
        ],
        [
            'label' => 'Parametrización',
            'pattern' => 'parametrizacion.*',
            'items' => [
                ['label' => 'Cliente', 'route' => 'parametrizacion.clientes.index', 'active' => 'parametrizacion.clientes.*'],
                ['label' => 'Bus', 'route' => 'parametrizacion.buses.index', 'active' => 'parametrizacion.buses.*'],
                ['label' => 'Conductor', 'route' => 'parametrizacion.conductores.index', 'active' => 'parametrizacion.conductores.*'],
                ['label' => 'Ruta', 'route' => 'parametrizacion.rutas.index', 'active' => 'parametrizacion.rutas.*'],
                ['label' => 'Tipo encomienda', 'route' => 'parametrizacion.tipoencomiendas.index', 'active' => 'parametrizacion.tipoencomiendas.*'],
                ['label' => 'Tipo incidencia', 'route' => 'parametrizacion.tipoincidencias.index', 'active' => 'parametrizacion.tipoincidencias.*'],
                ['label' => 'Notificacion', 'route' => 'transaccional.notificaciones.index', 'active' => 'transaccional.notificaciones.*'],

                ],
        ],
        [
            'label' => 'Transaccional',
            'pattern' => 'transaccional.*',
            'items' => [
                ['label' => 'Reserva', 'route' => 'transaccional.reservas.index', 'active' => 'transaccional.reservas.*'],
                ['label' => 'Boleto', 'route' => 'transaccional.boletos.index', 'active' => 'transaccional.boletos.*'],
                ['label' => 'Viaje', 'route' => 'transaccional.viajes.index', 'active' => 'transaccional.viajes.*'],
                ['label' => 'Asignación de conductor', 'route' => 'transaccional.asignacionconductor.index', 'active' => 'transaccional.asignacionconductor.*'],
                ['label' => 'Incidencia', 'route' => 'transaccional.incidenciaviaje.index', 'active' => 'transaccional.incidenciaviaje.*'],
                ['label' => 'Encomienda', 'route' => 'transaccional.encomiendas.index', 'active' => 'transaccional.encomiendas.*'],
                
            ],
        ],
        [
            'label' => 'Reportes',
            'pattern' => 'Reportes.*',
            'items' => [
                ['label' => 'Resessadrva', 'route' => 'transaccional.reservas.index', 'active' => 'transaccional.reservas.*'],
                ['label' => 'Boleto', 'route' => 'transaccional.boletos.index', 'active' => 'transaccional.boletos.*'],
                ['label' => 'Viaje', 'route' => 'transaccional.viajes.index', 'active' => 'transaccional.viajes.*'],
                ['label' => 'Asignación de conductor', 'route' => 'transaccional.asignacionconductor.index', 'active' => 'transaccional.asignacionconductor.*'],
                ['label' => 'Incidencia', 'route' => 'transaccional.incidenciaviaje.index', 'active' => 'transaccional.incidenciaviaje.*'],
                ['label' => 'Encomienda', 'route' => 'transaccional.encomiendas.index', 'active' => 'transaccional.encomiendas.*'],
                
            ],
        ],
    ];
@endphp

<aside class="app-sidebar">
    <div class="mb-6 rounded-3xl border border-white/10 bg-white/10 p-4 shadow-lg shadow-slate-950/10 backdrop-blur">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">PValle</p>
        <h2 class="mt-2 text-xl font-semibold text-white">Panel de gestión</h2>
        <p class="mt-1 text-sm text-slate-300">Operaciones, seguridad y viajes en un solo lugar.</p>
    </div>

    <nav class="space-y-3">
        @foreach ($navigationGroups as $group)
            <div
                x-data="{ open: {{ request()->routeIs($group['pattern']) ? 'true' : 'false' }} }"
                class="rounded-3xl border border-white/10 bg-white/5 p-2"
            >
                <button type="button" @click="open = ! open" class="sidebar-group-trigger">
                    <span>{{ $group['label'] }}</span>
                    <span class="text-lg leading-none text-slate-300" x-text="open ? '−' : '+'"></span>
                </button>

                <div x-show="open" x-cloak class="sidebar-group-panel">
                    @foreach ($group['items'] as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs($item['active']),
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
