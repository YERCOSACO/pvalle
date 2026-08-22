<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buses</h2>
            <a href="{{ route('parametrizacion.buses.create') }}"
               class="btn-primary">
                Agregar Bus
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
<form method="GET" action="{{ route('parametrizacion.buses.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por placa o modelo..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="btn-primary">
        Buscar Bus  
    </button>
    @if(request('search'))
        <a href="{{ route('parametrizacion.buses.index') }}"
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
                        <th class="px-6 py-3 text-left">Placa</th>
                        <th class="px-6 py-3 text-left">Capacidad</th>
                        <th class="px-6 py-3 text-left">Modelo</th>
                        <th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($buses as $bus)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $bus->placa }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $bus->capacidad }} asientos</td>
                        <td class="px-6 py-3 text-gray-500">{{ $bus->modelo ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $bus->tipo_bus ?? '—' }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('parametrizacion.buses.edit', $bus) }}"
                               class="btn-warning btn-sm">
                                Modificar Bus
                            </a>
                            <form action="{{ route('parametrizacion.buses.destroy', $bus) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este bus?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                Eliminar Bus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            Sin buses registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($buses->hasPages())
                <div class="px-6 py-4 border-t">{{ $buses->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>