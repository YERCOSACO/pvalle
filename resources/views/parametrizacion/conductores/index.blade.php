<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conductores</h2>
            <a href="{{ route('parametrizacion.conductores.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Agregar Conductor
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
<form method="GET" action="{{ route('parametrizacion.conductores.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por nombre o licencia..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
        Buscar Conductor
    </button>
    @if(request('search'))
        <a href="{{ route('parametrizacion.conductores.index') }}"
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
                        <th class="px-6 py-3 text-left">Licencia</th>
                        <th class="px-6 py-3 text-left">Teléfono</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($conductores as $conductor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $conductor->nombre_completo }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $conductor->licencia }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $conductor->telefono ?? '—' }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('parametrizacion.conductores.edit', $conductor) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Modificar Conductor
                            </a>
                            <form action="{{ route('parametrizacion.conductores.destroy', $conductor) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este conductor?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar Conductor
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Sin conductores registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($conductores->hasPages())
                <div class="px-6 py-4 border-t">{{ $conductores->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>