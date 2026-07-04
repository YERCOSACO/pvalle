<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asientos por Viaje</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        <form method="GET" action="{{ route('transaccional.asientoviaje.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por origen o destino..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.asientoviaje.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
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
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->capacidad }} asientos</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('transaccional.asientoviaje.show', $viaje) }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs">
                                Ver Asientos
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Sin viajes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>