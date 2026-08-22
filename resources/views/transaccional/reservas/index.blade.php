<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reservas</h2>
            <a href="{{ route('transaccional.reservas.create') }}"
               class="btn-primary">
                Agregar Reserva
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.reservas.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por cliente..."
                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="btn-primary">
                Buscar Reserva
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.reservas.index') }}"
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
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Boletos</th>
                        <th class="px-6 py-3 text-left">Total</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reservas as $reserva)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $reserva->cliente->nombre_completo }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $reserva->fecha_reserva->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-3 text-gray-500">
                            {{ $reserva->boletos->count() }} / {{ $reserva->cantidad }}
                        </td>
                        <td class="px-6 py-3 text-gray-500">Bs {{ number_format($reserva->total_pagar, 2) }}</td>
                        <td class="px-6 py-3">
                            @php
                                $colores = ['pendiente' => 'bg-yellow-100 text-yellow-700', 'confirmada' => 'bg-green-100 text-green-700', 'cancelada' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="{{ $colores[$reserva->estado] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $reserva->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('transaccional.reservas.edit', $reserva) }}"
                               class="btn-warning btn-sm">
                                Modificar Reserva
                            </a>
                            <form action="{{ route('transaccional.reservas.destroy', $reserva) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta reserva?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                Eliminar Reserva
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Sin reservas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($reservas->hasPages())
                <div class="px-6 py-4 border-t">{{ $reservas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>