<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Parametrización</p>
            <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">Administrar asientos por VIAJE-BUS</h2>
            <p class="mt-1 text-sm text-gray-500">Busca un bus y abre la maqueta del viaje para modificar o eliminar sus asientos.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('asiento'))
            <div class="mb-4 rounded-lg bg-rose-100 p-4 text-sm text-rose-700" role="alert">
                {{ $errors->first('asiento') }}
            </div>
        @endif

        <form method="GET" action="{{ route('parametrizacion.asientoviaje.gestion') }}" class="mb-4 flex flex-col gap-2 sm:flex-row">
            <label for="search" class="sr-only">Buscar bus por placa o modelo</label>
            <input id="search" type="search" name="search" value="{{ $filtros['search'] ?? '' }}"
                   placeholder="Buscar bus por placa, modelo o tipo..."
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
            <button type="submit" class="btn-primary">Buscar</button>
            @if(!empty($filtros['search']))
                <a href="{{ route('parametrizacion.asientoviaje.gestion') }}" class="btn-secondary text-center">Limpiar</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead class="border-b bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                                    <th class="px-6 py-3 text-left">Bus</th>
                                    <th class="px-6 py-3 text-left">Modelo</th>
                                    <th class="px-6 py-3 text-left">Tipo</th>
                                    <th class="px-6 py-3 text-left">Capacidad</th>
                            <th class="px-6 py-3 text-left">Ruta</th>
                            <th class="px-6 py-3 text-left">Viaje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($buses as $bus)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-semibold text-gray-800">{{ $bus->placa }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $bus->modelo ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $bus->tipo_bus ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $bus->capacidad }} asientos</td>
                                <td class="px-6 py-3 font-medium text-gray-700">
                                    @if($bus->viajes->isNotEmpty())
                                        {{ $bus->viajes->first()->ruta?->origen ?? 'Origen no disponible' }} - {{ $bus->viajes->first()->ruta?->destino ?? 'Destino no disponible' }}
                                    @else
                                        <span class="text-gray-400">Sin viajes</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @forelse($bus->viajes as $viaje)
                                <a href="{{ route('parametrizacion.asientoviaje.viaje', $viaje) }}"
                                        aria-label="Ver maqueta del viaje más reciente"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-teal-700 hover:shadow active:scale-95">

                                    <span>Ver maqueta</span>
                                </a>
                                    @empty
                                        <span class="text-gray-400">Sin viajes activos</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">No hay buses que coincidan con la búsqueda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($buses->hasPages())
                <div class="border-t px-6 py-4">{{ $buses->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
