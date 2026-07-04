<aside class="w-64 bg-gray-800 text-gray-200 min-h-screen px-4 py-6">

    <h2 class="text-white text-lg font-bold mb-6 px-2">PValle</h2>

    <nav class="space-y-1">

        {{-- SEGURIDAD --}}
        <div x-data="{ open: {{ request()->routeIs('seguridad.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex justify-between items-center px-3 py-2 rounded-md hover:bg-gray-700 text-sm">
                <span>Seguridad</span>
                <span x-text="open ? '−' : '+'"></span>
            </button>
            <div x-show="open" class="ml-4 mt-1 space-y-1">
                <a href="{{ route('seguridad.usuarios.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('seguridad.usuarios.*') ? 'bg-gray-700 text-white' : '' }}">
                    Usuario
                </a>
                <a href="{{ route('seguridad.roles.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('seguridad.roles.*') ? 'bg-gray-700 text-white' : '' }}">
                    Rol
                </a>
                <a href="{{ route('seguridad.usuariorol.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('seguridad.usuariorol.*') ? 'bg-gray-700 text-white' : '' }}">
                    Usuario Rol
                </a>
            </div>
        </div>

        {{-- PARAMETRIZACIÓN --}}
        <div x-data="{ open: {{ request()->routeIs('parametrizacion.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex justify-between items-center px-3 py-2 rounded-md hover:bg-gray-700 text-sm">
                <span>Parametrización</span>
                <span x-text="open ? '−' : '+'"></span>
            </button>
            <div x-show="open" class="ml-4 mt-1 space-y-1">
                <a href="{{ route('parametrizacion.clientes.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.clientes.*') ? 'bg-gray-700 text-white' : '' }}">
                    Cliente
                <a href="{{ route('parametrizacion.buses.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.buses.*') ? 'bg-gray-700 text-white' : '' }}">
                    Bus
                </a>
                <a href="{{ route('parametrizacion.conductores.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.conductores.*') ? 'bg-gray-700 text-white' : '' }}">
                    Conductor
                </a>
                <a href="{{ route('parametrizacion.rutas.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.rutas.*') ? 'bg-gray-700 text-white' : '' }}">
                    Ruta
                </a>
                <a href="{{ route('parametrizacion.tipoencomiendas.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.tipoencomiendas.*') ? 'bg-gray-700 text-white' : '' }}">
                    Tipo Encomienda
                </a>
                <a href="{{ route('parametrizacion.tipoincidencias.index') }}"
                   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('parametrizacion.tipoincidencias.*') ? 'bg-gray-700 text-white' : '' }}">
                    Tipos Incidencia
                </a>
            </div>
        </div>

    {{-- TRANSACCIONAL --}}
<div x-data="{ open: {{ request()->routeIs('transaccional.*') ? 'true' : 'false' }} }">
<button @click="open = !open"
        class="w-full flex justify-between items-center px-3 py-2 rounded-md hover:bg-gray-700 text-sm">
    <span>Transaccional</span>
    <span x-text="open ? '−' : '+'"></span>
</button>
<div x-show="open" class="ml-4 mt-1 space-y-1">
    <a href="{{ route('transaccional.viajes.index') }}"
       class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.viajes.*') ? 'bg-gray-700 text-white' : '' }}">
        Viaje
    </a>
    <a href="{{ route('transaccional.asignacionconductor.index') }}"
       class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.asignacionconductor.*') ? 'bg-gray-700 text-white' : '' }}">
        Asignación de Conductor
    </a>
    <a href="{{ route('transaccional.incidenciaviaje.index') }}"
       class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.incidenciaviaje.*') ? 'bg-gray-700 text-white' : '' }}">
        Incidencias en Viaje
    </a>
    <a href="{{ route('transaccional.encomiendas.index') }}"
       class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.encomiendas.*') ? 'bg-gray-700 text-white' : '' }}">
        Encomienda
    </a>
    <a href="{{ route('transaccional.notificaciones.index') }}"
   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.notificaciones.*') ? 'bg-gray-700 text-white' : '' }}">
    Notificacion
    </a>
    <a href="{{ route('transaccional.asientoviaje.index') }}"
   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.asientoviaje.*') ? 'bg-gray-700 text-white' : '' }}">
    Asientos por Viaje
    </a>
    <a href="{{ route('transaccional.reservas.index') }}"
   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.reservas.*') ? 'bg-gray-700 text-white' : '' }}">
    Reserva
</a>
<a href="{{ route('transaccional.boletos.index') }}"
   class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700 {{ request()->routeIs('transaccional.boletos.*') ? 'bg-gray-700 text-white' : '' }}">
    Boleto
</a>
    <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700">Reservas</a>
    <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700">Boletos</a>
    <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700">Pagos</a>
</div>
</div>

        {{-- REPORTE --}}
        <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700">
            📊 Reporte
        </a>

        {{-- ESTADÍSTICA --}}
        <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-700">
            📈 Estadística
        </a>

    </nav>
</aside>