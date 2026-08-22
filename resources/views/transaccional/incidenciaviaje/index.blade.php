<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Incidencias en Viajes</h2>
            <a href="{{ route('transaccional.incidenciaviaje.create') }}"
               class="btn-primary">
                Agregar Incidencia
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.incidenciaviaje.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por tipo de incidencia..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="btn-primary">
                Buscar Incidencia
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.incidenciaviaje.index') }}"
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
                        <th class="px-6 py-3 text-left">Viaje</th>
                        <th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-left">Inicio</th>
                        <th class="px-6 py-3 text-left">Fin</th>
                        <th class="px-6 py-3 text-left">Estado actual del viaje</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($incidencias as $incidencia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">
                            {{ $incidencia->viaje->ruta->nombre_ruta }} — {{ $incidencia->viaje->fecha_viaje->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $colores = ['Leve' => 'bg-yellow-100 text-yellow-700', 'Moderado' => 'bg-orange-100 text-orange-700', 'Grave' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="{{ $colores[$incidencia->tipoIncidencia->nivel_gravedad] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full">
                                {{ $incidencia->tipoIncidencia->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $incidencia->fecha_inicio->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $incidencia->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $coloresViaje = ['programado' => 'bg-blue-100 text-blue-700', 'retrasado' => 'bg-orange-100 text-orange-700', 'cancelado' => 'bg-red-100 text-red-700', 'finalizado' => 'bg-green-100 text-green-700'];
                            @endphp
                            <span class="{{ $coloresViaje[$incidencia->viaje->estado] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $incidencia->viaje->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('transaccional.incidenciaviaje.edit', $incidencia) }}"
                               class="btn-warning btn-sm">
                                Modificar Incidencia
                            </a>
                            <form action="{{ route('transaccional.incidenciaviaje.destroy', $incidencia) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta incidencia?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                    Eliminar Incidencia
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Sin incidencias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($incidencias->hasPages())
                <div class="px-6 py-4 border-t">{{ $incidencias->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>