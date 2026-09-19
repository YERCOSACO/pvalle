@php
    $navigationGroups = [
        [
            'label' => 'Seguridad',
            'short' => '01',
            'pattern' => 'seguridad.*',
            'items' => [
                ['label' => 'ADMINISTRAR USUARIO', 'route' => 'seguridad.usuarios.index', 'active' => 'seguridad.usuarios.*', 'permission' => 'usuarios.ver'],
                ['label' => 'ADMINISTRAR ROL', 'route' => 'seguridad.roles.index', 'active' => 'seguridad.roles.*', 'permission' => 'roles.ver'],
                ['label' => 'ADMINISTRAR  USUARIO_ROL', 'route' => 'seguridad.usuariorol.index', 'active' => 'seguridad.usuariorol.*', 'permission' => 'usuariorol.ver'],
            ],
        ],
        [
            'label' => 'Parametrización',
            'short' => '02',
            'pattern' => 'parametrizacion.*',
            'items' => [
                ['label' => 'ADMINISTRAR CLIENTE', 'route' => 'parametrizacion.clientes.index', 'active' => 'parametrizacion.clientes.*', 'permission' => 'clientes.ver'],
                ['label' => 'ADMINISTRAR BUS', 'route' => 'parametrizacion.buses.index', 'active' => 'parametrizacion.buses.*', 'permission' => 'buses.ver'],
                ['label' => 'ADMINISTRAR CONDUCTOR', 'route' => 'parametrizacion.conductores.index', 'active' => 'parametrizacion.conductores.*', 'permission' => 'conductores.ver'],
                ['label' => 'ADMINISTRAR RUTA', 'route' => 'parametrizacion.rutas.index', 'active' => 'parametrizacion.rutas.*', 'permission' => 'rutas.ver'],
                ['label' => 'ADMINISTRAR TIPO_ENCOMIENDA', 'route' => 'parametrizacion.tipoencomiendas.index', 'active' => 'parametrizacion.tipoencomiendas.*', 'permission' => 'tipoencomiendas.ver'],
                ['label' => 'ADMINISTRAR TIPOS_INCIDENCIA', 'route' => 'parametrizacion.tipoincidencias.index', 'active' => 'parametrizacion.tipoincidencias.*', 'permission' => 'tipoincidencias.ver'],
         //       ['label' => 'ADMINISTRAR ASIENTO_VIAJE', 'route' => 'parametrizacion.asientoviaje.index', 'active' => 'parametrizacion.asientoviaje.*', 'permission' => 'asientoviaje.ver'],
                ['label' => 'ADMINISTRAR NOTIFICACION', 'route' => 'transaccional.notificaciones.index', 'active' => 'transaccional.notificaciones.*', 'permission' => 'notificaciones.ver'],

                ],
        ],
        [
            'label' => 'Transaccional',
            'short' => '03',
            'pattern' => 'transaccional.*',
            'items' => [
                ['label' => 'ADMINISTRAR RESERVA', 'route' => 'transaccional.reservas.index', 'active' => 'transaccional.reservas.*', 'permission' => 'reservas.ver'],
                ['label' => 'ADMINISTRAR BOLETO', 'route' => 'transaccional.boletos.index', 'active' => 'transaccional.boletos.*', 'permission' => 'boletos.ver'],
                ['label' => 'ADMINISTRAR VIAJE', 'route' => 'transaccional.viajes.index', 'active' => 'transaccional.viajes.*', 'permission' => 'viajes.ver'],
                ['label' => 'ADMINISTRAR ASIGNACIÓN_CONDUCTOR', 'route' => 'transaccional.asignacionconductor.index', 'active' => 'transaccional.asignacionconductor.*', 'permission' => 'asignacionconductor.ver'],
                ['label' => 'ADMINISTRAR INCIDENCIA', 'route' => 'transaccional.incidenciaviaje.index', 'active' => 'transaccional.incidenciaviaje.*', 'permission' => 'incidenciaviaje.ver'],
                ['label' => 'ADMINISTRAR ENCOMIENDA', 'route' => 'transaccional.encomiendas.index', 'active' => 'transaccional.encomiendas.*', 'permission' => 'encomiendas.ver'],
                ],
        ],
                [
            'label' => 'Reporte',
            'short' => '04',
            'pattern' => 'transaccional.*',
            'items' => [
                ['label' => 'REPORTE ASIENTO_VIAJE', 'route' => 'parametrizacion.asientoviaje.index', 'active' => 'parametrizacion.asientoviaje.*', 'permission' => 'asientoviaje.ver'],
 ],
        ],
    ];
@endphp

<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="min-w-0">
            <p class="text-[0.65rem] font-bold uppercase tracking-[0.28em] text-slate-400">MI_VALLE</p>
            <h2 class="mt-1 truncate text-base font-semibold text-white">PANEL DE CONTROL</h2>
        </div>
    </div>

    <nav class="space-y-2">
        @foreach ($navigationGroups as $group)
            @php($itemsVisibles = collect($group['items'])->filter(fn ($item) => auth()->user()->can($item['permission'])))
            @if($itemsVisibles->isNotEmpty())
            <div
                x-data="{ open: {{ request()->routeIs($group['pattern']) ? 'true' : 'false' }} }"
                class="sidebar-group"
            >
                <button type="button" @click="open = ! open" class="sidebar-group-trigger">
                    <span class="flex items-center gap-3"><span class="sidebar-group-number">{{ $group['short'] }}</span>{{ $group['label'] }}</span>
                    <span class="text-base leading-none text-slate-400" x-text="open ? '−' : '+'"></span>
                </button>

                <div x-show="open" x-cloak class="sidebar-group-panel">
                    @foreach ($itemsVisibles as $item)
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
            @endif
        @endforeach
    </nav>

</aside>
