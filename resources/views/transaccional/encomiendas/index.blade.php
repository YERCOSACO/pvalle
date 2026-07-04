<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Encomiendas</h2>
            <a href="{{ route('transaccional.encomiendas.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Agregar Encomienda
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.encomiendas.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por destinatario o remitente..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                Buscar Encomienda
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.encomiendas.index') }}"
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
                        <th class="px-6 py-3 text-left">Remitente</th>
                        <th class="px-6 py-3 text-left">Destinatario</th>
                        <th class="px-6 py-3 text-left">Ruta</th>
                        <th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-left">Cant.</th>
                        <th class="px-6 py-3 text-left">Total</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($encomiendas as $encomienda)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $encomienda->nombre_remitente }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $encomienda->destinatario_nombre }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $encomienda->viaje->ruta->nombre_ruta }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $encomienda->tipoEncomienda->nombre }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $encomienda->cantidad }}</td>
                        <td class="px-6 py-3 text-gray-500">Bs {{ number_format($encomienda->total_pagar, 2) }}</td>
                        <td class="px-6 py-3">
                            @php
                                $colores = [
                                    'recibido'    => 'bg-yellow-100 text-yellow-700',
                                    'en transito' => 'bg-blue-100 text-blue-700',
                                    'entregado'   => 'bg-green-100 text-green-700',
                                    'cancelado'   => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="{{ $colores[$encomienda->estado] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $encomienda->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('transaccional.encomiendas.edit', $encomienda) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Modificar Encomienda
                            </a>
                            <form action="{{ route('transaccional.encomiendas.destroy', $encomienda) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta encomienda?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar Encomienda
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">Sin encomiendas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($encomiendas->hasPages())
                <div class="px-6 py-4 border-t">{{ $encomiendas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>