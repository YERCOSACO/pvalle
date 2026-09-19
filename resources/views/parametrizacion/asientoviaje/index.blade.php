<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asientos por Viaje</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        <form method="GET" action="{{ route('parametrizacion.asientoviaje.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por origen o destino..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="btn-primary">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('parametrizacion.asientoviaje.index') }}"
                   class="btn-secondary">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Ruta</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Bus</th>
                        <th class="px-6 py-3 text-left">Conductores asignados</th>
                        <th class="px-6 py-3 text-left">Capacidad</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($viajes as $viaje)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $viaje->ruta->nombre_ruta }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->fecha_viaje->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->placa }}</td>
                        <td class="px-6 py-3 text-gray-600">
                            @forelse($viaje->asignaciones as $asignacion)
                                <div>
                                    <span class="font-medium">{{ $asignacion->conductor?->nombre_completo ?? $asignacion->nombre_ayudante ?? 'Ayudante' }}</span>
                                    <span class="text-xs text-gray-400">({{ $asignacion->tipo_asignacion }})</span>
                                </div>
                            @empty
                                <span class="text-gray-400">Sin conductores asignados</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->capacidad }} asientos</td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">

<a href="{{ route('parametrizacion.asientoviaje.imprimir', $viaje) }}"
   target="_blank"
   class="btn btn-success btn-sm">
    Imprimir Reporte
</a>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Sin viajes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>