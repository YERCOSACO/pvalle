<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tipos de Encomienda</h2>
            <a href="{{ route('parametrizacion.tipoencomiendas.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Agregar TipoEncomienda
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
<form method="GET" action="{{ route('parametrizacion.tipoencomiendas.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por nombre..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
        Buscar TipoEncomienda
    </button>
    @if(request('search'))
        <a href="{{ route('parametrizacion.tipoencomiendas.index') }}"
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
                        <th class="px-6 py-3 text-left">Precio</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tipoEncomiendas as $tipoEncomienda)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $tipoEncomienda->nombre }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $tipoEncomienda->descripcion ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">Bs {{ number_format($tipoEncomienda->precio, 2) }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('parametrizacion.tipoencomiendas.edit', $tipoEncomienda) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Modificar TipoEncomienda
                            </a>
                            <form action="{{ route('parametrizacion.tipoencomiendas.destroy', $tipoEncomienda) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este tipo?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar TipoEncomienda
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Sin tipos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($tipoEncomiendas->hasPages())
                <div class="px-6 py-4 border-t">{{ $tipoEncomiendas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>