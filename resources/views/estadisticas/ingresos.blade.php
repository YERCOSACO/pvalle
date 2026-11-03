<x-app-layout>
    @php
        $nombresMeses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $mesActual = $nombresMeses[(int) $desde->format('n')];
        $maximoRuta = max((float) $detalle['por_ruta']->max('ingresos'), 1);
        $coloresMetodo = ['#0f766e', '#0284c7', '#f59e0b', '#e11d48', '#7c3aed', '#64748b'];
        $desplazamiento = 0;
        $graficosMetodos = $detalle['por_metodo']->values()->map(function ($metodo, $indice) use (&$desplazamiento, $detalle, $coloresMetodo) {
            $porcentaje = $detalle['boletos'] > 0 ? $metodo['boletos'] * 100 / $detalle['boletos'] : 0;
            $segmento = [
                'nombre' => $metodo['nombre'],
                'boletos' => $metodo['boletos'],
                'porcentaje' => $porcentaje,
                'desplazamiento' => $desplazamiento,
                'color' => $coloresMetodo[$indice % count($coloresMetodo)],
            ];
            $desplazamiento += $porcentaje;

            return $segmento;
        });
    @endphp

    <x-slot name="header">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-teal-800 hover:text-teal-950">← Panel principal</a>
            <p class="mt-3 text-xs font-bold uppercase tracking-[0.16em] text-sky-700">Detalle mensual · {{ $mesActual }} {{ $anio }}</p>
            <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Ingresos por boletos</h2>
            <p class="mt-2 text-sm text-slate-600">Ingresos de boletos activos y confirmados, agrupados por la fecha de pago.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-6">
        @include('estadisticas._periodo', ['action' => 'estadisticas.ingresos'])

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($tarjetas as $tarjeta)
                <x-kpi-card
                    :titulo="$tarjeta['titulo']"
                    :valor="$tarjeta['valor']"
                    :subtitulo="$tarjeta['subtitulo']"
                    :variacion="$tarjeta['variacion']"
                    :formato="$tarjeta['formato']"
                    :ayuda="$tarjeta['ayuda']"
                    :mostrar-variacion="$loop->first"
                />
            @endforeach
        </div>

        @php
            $valoresDiarios = $detalle['por_dia']->pluck('ingresos')->all();
            $maximoDiario = max(max($valoresDiarios), 1);
            $cantidadDias = count($valoresDiarios);
            $puntosDiarios = collect($valoresDiarios)->map(fn ($valor, $indice) => [
                'x' => $cantidadDias > 1 ? 20 + 560 * $indice / ($cantidadDias - 1) : 300,
                'y' => 140 - 120 * $valor / $maximoDiario,
                'valor' => $valor,
                'dia' => $indice + 1,
            ]);
            $lineaDiaria = $puntosDiarios->map(fn ($punto) => $punto['x'].','.$punto['y'])->implode(' ');
            $areaDiaria = $puntosDiarios->isNotEmpty()
                ? $puntosDiarios->first()['x'].',140 '.$lineaDiaria.' '.$puntosDiarios->last()['x'].',140'
                : '';
        @endphp

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h3 class="text-lg font-extrabold text-slate-950">Ingresos por día</h3>
                <p class="mt-1 text-sm text-slate-500">Evolución diaria de los boletos pagados durante {{ $mesActual }}.</p>
            </div>
            <div class="overflow-x-auto">
                <svg viewBox="0 0 600 175" class="h-56 min-w-[600px] w-full" role="img" aria-label="Gráfica diaria de ingresos por boletos confirmados">
                    <line x1="20" y1="140" x2="580" y2="140" stroke="#cbd5e1" stroke-width="1"></line>
                    <line x1="20" y1="80" x2="580" y2="80" stroke="#e2e8f0" stroke-width="1"></line>
                    <line x1="20" y1="20" x2="580" y2="20" stroke="#e2e8f0" stroke-width="1"></line>
                    <polygon points="{{ $areaDiaria }}" fill="#0f766e" fill-opacity="0.12"></polygon>
                    <polyline points="{{ $lineaDiaria }}" fill="none" stroke="#0f766e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    @foreach ($puntosDiarios->filter(fn ($punto, $indice) => $indice === 0 || $indice === $cantidadDias - 1 || $punto['dia'] % 5 === 0) as $punto)
                        <circle cx="{{ $punto['x'] }}" cy="{{ $punto['y'] }}" r="4" fill="#0f766e">
                            <title>Día {{ $punto['dia'] }}: Bs {{ number_format($punto['valor'], 2, ',', '.') }}</title>
                        </circle>
                        <text x="{{ $punto['x'] }}" y="162" text-anchor="middle" font-size="10" fill="#64748b">{{ $punto['dia'] }}</text>
                    @endforeach
                </svg>
            </div>
            <p class="text-xs text-slate-500">Cada punto representa el total diario de pagos confirmados. Los días sin ventas aparecen en cero.</p>
        </section>

        <div class="grid gap-6 xl:grid-cols-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3 sm:p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-extrabold text-slate-950">Ingresos por ruta</h3>
                    <p class="mt-1 text-sm text-slate-500">Comparación de ventas confirmadas en {{ $mesActual }}.</p>
                </div>
                @forelse ($detalle['por_ruta'] as $ruta)
                    <div class="mb-5 last:mb-0">
                        <div class="mb-2 flex flex-wrap justify-between gap-2 text-sm">
                            <span class="font-semibold text-slate-700">{{ $ruta['nombre'] }}</span>
                            <span class="text-slate-600">{{ $ruta['boletos'] }} boletos · <strong class="text-slate-900">Bs {{ number_format($ruta['ingresos'], 2, ',', '.') }}</strong></span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-teal-600" style="width: {{ $ruta['ingresos'] * 100 / $maximoRuta }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-500">No hay boletos confirmados para el periodo seleccionado.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 sm:p-6">
                <h3 class="text-lg font-extrabold text-slate-950">Métodos de pago</h3>
                <p class="mt-1 text-sm text-slate-500">Cantidad de boletos e importe confirmado.</p>
                @if ($detalle['boletos'] > 0)
                    <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row">
                        <div class="relative h-36 w-36 shrink-0">
                            <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-label="Distribución de boletos por método de pago">
                                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#e2e8f0" stroke-width="6"></circle>
                                @foreach ($graficosMetodos as $segmento)
                                    <circle cx="21" cy="21" r="15.915" fill="none" stroke="{{ $segmento['color'] }}" stroke-width="6"
                                            stroke-dasharray="{{ $segmento['porcentaje'] }} {{ 100 - $segmento['porcentaje'] }}"
                                            stroke-dashoffset="{{ -$segmento['desplazamiento'] }}"></circle>
                                @endforeach
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xl font-extrabold text-slate-900">{{ $detalle['boletos'] }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($graficosMetodos as $segmento)
                                <p class="flex items-center gap-2 text-xs text-slate-600">
                                    <span class="inline-block h-2.5 w-2.5 rounded-sm" style="background-color: {{ $segmento['color'] }}"></span>
                                    {{ $segmento['nombre'] }}: {{ $segmento['boletos'] }} ({{ number_format($segmento['porcentaje'], 1, ',', '.') }}%)
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse ($detalle['por_metodo'] as $metodo)
                        <div class="flex items-center justify-between gap-3 py-4 first:pt-0 last:pb-0">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $metodo['nombre'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $metodo['boletos'] }} boletos</p>
                            </div>
                            <p class="text-right text-sm font-bold text-slate-900">Bs {{ number_format($metodo['ingresos'], 2, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="py-5 text-sm text-slate-500">No hay pagos confirmados en este periodo.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <p class="text-xs text-slate-500">Los boletos pendientes o inactivos no se consideran ingresos. Los registros antiguos sin fecha de emisión se agrupan por su fecha de creación.</p>
    </div>
</x-app-layout>
