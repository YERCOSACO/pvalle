<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Operaciones</p>
            <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">Reporte de asientos por viaje</h2>
            <p class="mt-1 text-sm text-gray-500">Busca una salida y revisa su ocupación o imprime la lista de pasajeros.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4">
        <div class="pt-6">
            @include('reporte._navigation', ['active' => 'asientos'])
        </div>

        <div class="py-2">

        <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('parametrizacion.asientoviaje.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_auto_auto] lg:items-end">
                <div>
                    <label for="search" class="mb-1.5 block text-sm font-medium text-slate-700">Ruta</label>
                    <input id="search" type="search" name="search" value="{{ $filtros['search'] ?? '' }}"
                           placeholder="Origen o destino" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <div>
                    <label for="desde" class="mb-1.5 block text-sm font-medium text-slate-700">Desde</label>
                    <input id="desde" type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                           max="{{ $filtros['hasta'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <div>
                    <label for="hasta" class="mb-1.5 block text-sm font-medium text-slate-700">Hasta</label>
                    <input id="hasta" type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}"
                           min="{{ $filtros['desde'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <button type="submit" class="btn-primary w-full sm:w-auto">Buscar viajes</button>
                <a href="{{ route('parametrizacion.asientoviaje.index') }}" class="btn-secondary w-full text-center sm:w-auto">Limpiar</a>
            </form>
            @if($errors->has('desde') || $errors->has('hasta'))
                <p class="mt-3 text-sm font-medium text-rose-700" role="alert">La fecha inicial debe ser anterior o igual a la fecha final.</p>
            @endif
        </section>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Ruta</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Bus</th>
                        <th class="px-6 py-3 text-left">Conductores asignados</th>
                        <th class="px-6 py-3 text-left">Capacidad</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($viajes as $viaje)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $viaje->ruta?->nombre_ruta ?? 'Ruta no disponible' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->fecha_viaje?->format('d/m/Y') ?? '—' }} <span class="text-xs">{{ $viaje->hora_salida ? substr($viaje->hora_salida, 0, 5) : '' }}</span></td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus?->placa ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-600">
                            @forelse($viaje->asignaciones as $asignacion)
                                <div>
                                    <span class="font-medium">{{ $asignacion->conductor?->nombre_completo ?? $asignacion->nombre_ayudante ?? 'Ayudante' }}</span>
                                    <span class="text-xs text-gray-400">({{ $asignacion->tipo_asignacion }})</span>
                                </div>
                            @empty
                                <span class="text-gray-400">Sin conductores asignados</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $viaje->bus?->capacidad ?? 0 }} asientos</td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('parametrizacion.asientoviaje.show', $viaje) }}"
                                   aria-label="Ver asientos de {{ $viaje->ruta?->nombre_ruta ?? 'este viaje' }} del {{ $viaje->fecha_viaje?->format('d/m/Y') ?? '' }}"
                                   class="btn-primary btn-sm">Ver este viaje</a>
                                <a href="{{ route('parametrizacion.asientoviaje.imprimir', $viaje) }}"
                                   target="_blank" rel="noopener"
                                   aria-label="Imprimir reporte de {{ $viaje->ruta?->nombre_ruta ?? 'este viaje' }} del {{ $viaje->fecha_viaje?->format('d/m/Y') ?? '' }}"
                                   class="btn-success btn-sm">Imprimir este viaje</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No se encontraron viajes para estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($viajes->hasPages())
                <div class="px-6 py-4 border-t">{{ $viajes->links() }}</div>
            @endif
        </div>
        </div>
    </div>
</x-app-layout>