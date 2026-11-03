@php
    use App\Services\Reporte\ReporteHelper;
    $colores = ReporteHelper::coloresBoletos();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Centro de reportes</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Reporte de boletos</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta emisiones, estados y recaudación por período.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-8">
        @include('reporte._navigation', ['active' => 'boletos'])

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">Filtros del reporte</h2>
                <p class="mt-1 text-sm text-slate-500">Por defecto se muestra todo el año actual. Busca por pasajero, CI, asiento o ruta.</p>
            </div>
            <form method="GET" action="{{ route('reporte.boletos') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.5fr_auto_auto] lg:items-end">
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
                    <label for="buscar" class="mb-1.5 block text-sm font-medium text-slate-700">Buscar boleto</label>
                    <input id="buscar" type="search" name="buscar" value="{{ $buscar }}" placeholder="Pasajero, CI, asiento o ruta"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <button type="submit" class="btn-primary w-full sm:w-auto">Ver reporte</button>
                <a href="{{ route('reporte.boletos', ['desde' => $desde, 'hasta' => $hasta, 'buscar' => $buscar, 'imprimir' => 1]) }}"
                   target="_blank" rel="noopener" class="btn-success w-full sm:w-auto">Imprimir / Guardar PDF</a>
            </form>
            @if($errors->has('desde') || $errors->has('hasta'))
                <p class="mt-3 text-sm font-medium text-rose-700" role="alert">Revisa el rango: la fecha inicial debe ser anterior o igual a la fecha final.</p>
            @endif
        </section>

        <section aria-label="Resumen de boletos" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Boletos emitidos', 'value' => $resumen['total'], 'tone' => 'text-slate-950', 'border' => 'border-slate-300'],
                ['label' => 'Confirmados', 'value' => $resumen['confirmados'], 'tone' => 'text-emerald-700', 'border' => 'border-emerald-500'],
                ['label' => 'Pendientes', 'value' => $resumen['pendientes'], 'tone' => 'text-amber-700', 'border' => 'border-amber-500'],
                ['label' => 'Cancelados', 'value' => $resumen['cancelados'], 'tone' => 'text-rose-700', 'border' => 'border-rose-500'],
                ['label' => 'Recaudación confirmada', 'value' => 'Bs ' . number_format((float) $resumen['recaudado'], 2), 'tone' => 'text-teal-800', 'border' => 'border-teal-600'],
            ] as $indicador)
                <article class="rounded-2xl border border-slate-200 border-l-4 {{ $indicador['border'] }} bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $indicador['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold {{ $indicador['tone'] }}">{{ $indicador['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Detalle de boletos</h2>
                    <p class="text-sm text-slate-500">Emisión del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }} · {{ $boletos->count() }} registros</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Emitido</th>
                            <th class="px-4 py-3 font-semibold">Pasajero</th>
                            <th class="px-4 py-3 font-semibold">CI</th>
                            <th class="px-4 py-3 font-semibold">Ruta</th>
                            <th class="px-4 py-3 font-semibold">Fecha de viaje</th>
                            <th class="px-4 py-3 font-semibold">Asiento</th>
                            <th class="px-4 py-3 font-semibold">Pago</th>
                            <th class="px-4 py-3 text-right font-semibold">Precio</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold">Boleto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($boletos as $boleto)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-400">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $boleto->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $boleto->nombre_pasajero ?: 'No registrado' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $boleto->ci_pasajero ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $boleto->viaje?->ruta?->nombre_ruta ?? 'No disponible' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    <span class="block">{{ $boleto->viaje?->fecha_viaje?->format('d/m/Y') ?? '—' }}</span>
                                    @if($boleto->viaje?->hora_salida)
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ substr($boleto->viaje->hora_salida, 0, 5) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $boleto->numero_asiento ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $boleto->metodo_pago ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">Bs {{ number_format((float) $boleto->precio, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $colores[$boleto->estado] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($boleto->estado ?? 'Sin estado') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('transaccional.boletos.imprimir', $boleto) }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex whitespace-nowrap rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-teal-800"
                                       aria-label="Ver e imprimir boleto de {{ $boleto->nombre_pasajero ?: 'pasajero no registrado' }}, asiento {{ $boleto->numero_asiento }}">
                                        Ver / imprimir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-14 text-center">
                                    <p class="font-semibold text-slate-700">No hay boletos para este período.</p>
                                    <p class="mt-1 text-sm text-slate-500">Ajusta las fechas para consultar otros registros.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
