<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notificaciones</h2>
            <a href="{{ route('transaccional.notificaciones.create') }}"
               class="btn-primary">
                + Nueva
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.notificaciones.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por título o cliente..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="btn-primary">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.notificaciones.index') }}"
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
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Título</th>
                        <th class="px-6 py-3 text-left">Origen</th>
                        <th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notificaciones as $notificacion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $notificacion->cliente->nombre_completo }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $notificacion->titulo }}</td>
                        <td class="px-6 py-3 text-gray-500">
                            {{ $notificacion->origen_legible }}
                            @if($notificacion->origenDetalle)
                                — {{ $notificacion->origenDetalle }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $coloresTipo = ['info' => 'bg-blue-100 text-blue-700', 'alerta' => 'bg-orange-100 text-orange-700', 'urgente' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="{{ $coloresTipo[$notificacion->tipo] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $notificacion->tipo }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $coloresEstado = ['pendiente' => 'bg-yellow-100 text-yellow-700', 'enviada' => 'bg-blue-100 text-blue-700', 'leida' => 'bg-green-100 text-green-700'];
                            @endphp
                            <span class="{{ $coloresEstado[$notificacion->estado] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $notificacion->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('transaccional.notificaciones.edit', $notificacion) }}"
                               class="btn-warning btn-sm">
                                Editar
                            </a>
                            <form action="{{ route('transaccional.notificaciones.destroy', $notificacion) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta notificación?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Sin notificaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($notificaciones->hasPages())
                <div class="px-6 py-4 border-t">{{ $notificaciones->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>