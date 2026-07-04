<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rutas</h2>
            <a href="{{ route('parametrizacion.rutas.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Agregar Ruta
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
<form method="GET" action="{{ route('parametrizacion.rutas.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por origen o destino..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
        Buscar Ruta
    </button>
    @if(request('search'))
        <a href="{{ route('parametrizacion.rutas.index') }}"
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
                        <th class="px-6 py-3 text-left">Distancia</th>
                        <th class="px-6 py-3 text-left">Precio base</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rutas as $ruta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $ruta->nombre_ruta }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $ruta->distancia_km ? $ruta->distancia_km.' km' : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">Bs {{ number_format($ruta->precio_base, 2) }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('parametrizacion.rutas.edit', $ruta) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Modificar Ruta
                            </a>
                            <form action="{{ route('parametrizacion.rutas.destroy', $ruta) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta ruta?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar Ruta
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Sin rutas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($rutas->hasPages())
                <div class="px-6 py-4 border-t">{{ $rutas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>