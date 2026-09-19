<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asignación de Conductores</h2>
            <a href="{{ route('transaccional.asignacionconductor.create') }}"
               class="btn-primary">
                Agregar Asignación
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
<form method="GET" action="{{ route('transaccional.asignacionconductor.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por origen o destino del viaje..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="btn-primary">
        Buscar Asignación
    </button>
    @if(request('search'))
        <a href="{{ route('transaccional.asignacionconductor.index') }}"
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
                        <th class="px-6 py-3 text-left">Conductor Principal</th>
                        <th class="px-6 py-3 text-left">Conductor Relevo</th>
                        <th class="px-6 py-3 text-left">Ayudante</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($viajes as $viaje)
                    @php
                        $principal = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Principal');
                        $relevo    = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Relevo');
                        $ayudante  = $viaje->asignaciones->firstWhere('tipo_asignacion', 'Ayudante');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">
                            {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            {{ $principal->conductor->nombre_completo ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            {{ $relevo->conductor->nombre_completo ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            {{ $ayudante?->conductor?->nombre_completo ?? $ayudante?->nombre_ayudante ?? '— Sin ayudante' }}
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('transaccional.asignacionconductor.edit', $viaje) }}"
                               class="btn-warning btn-sm">
                                Modificar Asignación    
                            </a>
                            <form action="{{ route('transaccional.asignacionconductor.destroy', $viaje) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar toda la asignación de este viaje?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                Eliminar Asignación
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Sin asignaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>