<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tipos de Incidencia</h2>
            <a href="{{ route('parametrizacion.tipoincidencias.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Agregar TipoIncidencia
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
<form method="GET" action="{{ route('parametrizacion.tipoincidencias.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por nombre..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
        Buscar TipoIncidencia
    </button>
    @if(request('search'))
        <a href="{{ route('parametrizacion.tipoincidencias.index') }}"
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
                        <th class="px-6 py-3 text-left">Nombre</th>
                        <th class="px-6 py-3 text-left">Descripción</th>
                        <th class="px-6 py-3 text-left">Gravedad</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tipoIncidencias as $tipoIncidencia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $tipoIncidencia->nombre }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $tipoIncidencia->descripcion ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $colores = ['Leve' => 'bg-yellow-100 text-yellow-700', 'Moderado' => 'bg-orange-100 text-orange-700', 'Grave' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="{{ $colores[$tipoIncidencia->nivel_gravedad] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full">
                                {{ $tipoIncidencia->nivel_gravedad }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('parametrizacion.tipoincidencias.edit', $tipoIncidencia) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Modificar TipoIncidencia
                            </a>
                            <form action="{{ route('parametrizacion.tipoincidencias.destroy', $tipoIncidencia) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este tipo?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                Eliminar TipoIncidencia
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Sin tipos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($tipoIncidencias->hasPages())
                <div class="px-6 py-4 border-t">{{ $tipoIncidencias->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>