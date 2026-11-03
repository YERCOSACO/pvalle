@php
    use App\Services\Reporte\ReporteHelper;
    $colores = ReporteHelper::coloresEncomiendas();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Centro de reportes</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Reporte de encomiendas</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta los envíos, estados y montos registrados por período.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-8">
        @include('reporte._navigation', ['active' => 'encomiendas'])

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">Filtros del reporte</h2>
                <p class="mt-1 text-sm text-slate-500">Por defecto se muestran las encomiendas de todo el año actual. Filtra por viaje o busca por remitente/destinatario.</p>
            </div>
            <form method="GET" action="{{ route('reporte.encomiendas') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.5fr_1.5fr_auto_auto] lg:items-end">
                <div>
                    <label for="desde" class="mb-1.5 block text-sm font-medium text-slate-700">Desde</label>
                    <input id="desde" type="date" name="desde" value="{{ $desde }}" max="{{ $hasta }}" required
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <div>
                    <label for="hasta" class="mb-1.5 block text-sm font-medium text-slate-700">Hasta</label>
                    <input id="hasta" type="date" name="hasta" value="{{ $hasta }}" min="{{ $desde }}" required
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <div>
                    <label for="viaje_id" class="mb-1.5 block text-sm font-medium text-slate-700">Viaje</label>
                    <select id="viaje_id" name="viaje_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">Todos los viajes</option>
                        @foreach($viajesDisponibles as $opcionViaje)
                            <option value="{{ $opcionViaje->id }}" @selected((string) $opcionViaje->id === (string) $viajeId)>
                                {{ $opcionViaje->ruta?->nombre_ruta ?? 'Ruta no disponible' }} · {{ $opcionViaje->fecha_viaje?->format('d/m/Y') }} {{ substr((string) $opcionViaje->hora_salida, 0, 5) }} · {{ $opcionViaje->bus?->placa ?? 'Sin bus' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="buscar" class="mb-1.5 block text-sm font-medium text-slate-700">Buscar encomienda</label>
                    <input id="buscar" type="search" name="buscar" value="{{ $buscar }}" placeholder="Remitente, destinatario, tipo o código"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <button type="submit" class="btn-primary w-full sm:w-auto">Ver reporte</button>
                <a href="{{ route('reporte.encomiendas', ['desde' => $desde, 'hasta' => $hasta, 'viaje_id' => $viajeId, 'buscar' => $buscar, 'imprimir' => 1]) }}"
                   target="_blank" rel="noopener" class="btn-success w-full sm:w-auto">Imprimir / Guardar PDF</a>
            </form>
            @if($errors->has('desde') || $errors->has('hasta'))
                <p class="mt-3 text-sm font-medium text-rose-700" role="alert">Revisa el rango: la fecha inicial debe ser anterior o igual a la fecha final.</p>
            @endif
        </section>

        <section aria-label="Resumen de encomiendas" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Encomiendas', 'value' => $encomiendas->count(), 'tone' => 'text-slate-950', 'border' => 'border-slate-300'],
                ['label' => 'Recibidas', 'value' => $encomiendas->where('estado', 'recibido')->count(), 'tone' => 'text-blue-700', 'border' => 'border-blue-500'],
                ['label' => 'En tránsito', 'value' => $encomiendas->where('estado', 'en transito')->count(), 'tone' => 'text-amber-700', 'border' => 'border-amber-500'],
                ['label' => 'Entregadas', 'value' => $encomiendas->where('estado', 'entregado')->count(), 'tone' => 'text-emerald-700', 'border' => 'border-emerald-500'],
                ['label' => 'Ingresos registrados', 'value' => 'Bs ' . number_format((float) $encomiendas->sum('total_pagar'), 2), 'tone' => 'text-teal-800', 'border' => 'border-teal-600'],
            ] as $indicador)
                <article class="rounded-2xl border border-slate-200 border-l-4 {{ $indicador['border'] }} bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $indicador['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold {{ $indicador['tone'] }}">{{ $indicador['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-900">Detalle de encomiendas</h2>
                <p class="text-sm text-slate-500">Registros del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }} · {{ $encomiendas->count() }} registros</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Remitente</th>
                            <th class="px-4 py-3 font-semibold">Destinatario</th>
                            <th class="px-4 py-3 font-semibold">Tipo</th>
                            <th class="px-4 py-3 text-right font-semibold">Cantidad</th>
                            <th class="px-4 py-3 text-right font-semibold">Total</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold">Viaje</th>
                            <th class="px-4 py-3 font-semibold">Registrada</th>
                            <th class="px-4 py-3 font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($encomiendas as $encomienda)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono text-slate-400">#{{ $encomienda->id }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $encomienda->nombre_remitente ?: 'No registrado' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $encomienda->destinatario_nombre }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $encomienda->tipoEncomienda?->nombre ?? 'No disponible' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $encomienda->cantidad }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">Bs {{ number_format((float) $encomienda->total_pagar, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $colores[$encomienda->estado] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ ReporteHelper::nombreEstadoEncomienda($encomienda->estado ?? '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="font-medium">{{ $encomienda->viaje?->ruta?->nombre_ruta ?? 'No disponible' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $encomienda->viaje?->fecha_viaje?->format('d/m/Y') ?? '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $encomienda->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('transaccional.encomiendas.imprimir', $encomienda) }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex whitespace-nowrap rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                       aria-label="Ver e imprimir encomienda ENV-{{ str_pad($encomienda->id, 6, '0', STR_PAD_LEFT) }}">
                                        Ver / imprimir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-14 text-center">
                                    <p class="font-semibold text-slate-700">No hay encomiendas para estos filtros.</p>
                                    <p class="mt-1 text-sm text-slate-500">Prueba con otras fechas o con otro estado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
