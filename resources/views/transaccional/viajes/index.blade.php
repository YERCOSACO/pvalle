<x-app-layout>
    <x-slot name="header">
        <div class="section-toolbar">
            <div>
                <h2 class="page-title">Viajes</h2>
                <p class="page-subtitle">Consulta la programación, disponibilidad y estado operativo de cada salida.</p>
            </div>
            <a href="{{ route('transaccional.viajes.create') }}" class="btn-primary">
                Agregar viaje
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.viajes.index') }}" class="search-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por origen o destino...">
            <button type="submit" class="btn-primary">Buscar viaje</button>
            @if(request('search'))
                <a href="{{ route('transaccional.viajes.index') }}" class="btn-secondary">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="table-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-shell text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Ruta</th>
                        <th class="px-6 py-3 text-left">Bus</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Hora</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($viajes as $viaje)
                    <tr>
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $viaje->ruta->nombre_ruta }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->placa }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->fecha_viaje->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->hora_salida }}</td>
                        <td class="px-6 py-3">
                            @php
                                $colores = ['programado' => 'bg-blue-100 text-blue-700', 'en curso' => 'bg-yellow-100 text-yellow-700', 'finalizado' => 'bg-green-100 text-green-700', 'cancelado' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="status-pill {{ $colores[$viaje->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $viaje->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="table-actions">
                            <a href="{{ route('transaccional.viajes.edit', $viaje) }}"
                               class="btn-warning btn-sm">
                                Modificar
                            </a>
                            <form action="{{ route('transaccional.viajes.destroy', $viaje) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este viaje?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="empty-state">Sin viajes registrados.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
