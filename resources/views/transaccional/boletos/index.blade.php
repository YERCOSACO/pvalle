<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boletos por Viaje</h2>
            <a href="{{ route('transaccional.boletos.create') }}"
               class="btn-primary">
                + Nuevo Boleto
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.boletos.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por origen o destino..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="btn-primary">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.boletos.index') }}"
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
                        <th class="px-6 py-3 text-left">Capacidad</th>
                        <th class="px-6 py-3 text-left">Confirmados</th>
                        <th class="px-6 py-3 text-left">Pendientes QR</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($viajes as $viaje)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $viaje->ruta->nombre_ruta }}</td>
                        <td class="px-6 py-3 text-gray-500">
                            {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->placa }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus->capacidad }}</td>
                        <td class="px-6 py-3">
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-bold">
                                {{ $viaje->confirmados }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-bold">
                                {{ $viaje->pendientes }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <a href="{{ route('transaccional.boletos.por-viaje', $viaje) }}"
                               class="btn-primary btn-sm">
                                Ver asientos
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">Sin viajes registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>