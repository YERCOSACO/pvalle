<x-app-layout>
    @php
        $nombresMeses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $mesActual = $nombresMeses[(int) $desde->format('n')];
        $valoresDiarios = $detalle['por_dia']->pluck('viajes')->all();
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
        $coloresEstados = ['#0f766e', '#0284c7', '#f59e0b', '#e11d48', '#7c3aed', '#64748b'];
        $desplazamiento = 0;
        $graficosEstados = $detalle['por_estado']->values()->map(function ($estado, $indice) use (&$desplazamiento, $detalle, $coloresEstados) {
            $porcentaje = $detalle['total_viajes'] > 0 ? $estado['cantidad'] * 100 / $detalle['total_viajes'] : 0;
            $segmento = [
                'nombre' => $estado['nombre'],
                'cantidad' => $estado['cantidad'],
                'porcentaje' => $porcentaje,
                'desplazamiento' => $desplazamiento,
                'color' => $coloresEstados[$indice % count($coloresEstados)],
            ];
            $desplazamiento += $porcentaje;

            return $segmento;
        });
    @endphp

    <x-slot name="header">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-teal-800 hover:text-teal-950">← Panel principal</a>
            <p class="mt-3 text-xs font-bold uppercase tracking-[0.16em] text-sky-700">Detalle mensual · {{ $mesActual }} {{ $anio }}</p>
            <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Viajes por rutas</h2>
            <p class="mt-2 text-sm text-slate-600">Actividad, asientos vendidos y ocupación para cada ruta.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-6">
        @include('estadisticas._periodo', ['action' => 'estadisticas.viajes-ruta'])

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

        <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h3 class="text-lg font-extrabold text-slate-950">Viajes por día</h3>
                <p class="mt-1 text-sm text-slate-500">Cantidad de viajes registrados cada día de {{ $mesActual }}.</p>
            </div>
            <div class="overflow-x-auto">
                <svg viewBox="0 0 600 175" class="h-56 min-w-[600px] w-full" role="img" aria-label="Gráfica diaria de cantidad de viajes">
                    <line x1="20" y1="140" x2="580" y2="140" stroke="#cbd5e1" stroke-width="1"></line>
                    <line x1="20" y1="80" x2="580" y2="80" stroke="#e2e8f0" stroke-width="1"></line>
                    <line x1="20" y1="20" x2="580" y2="20" stroke="#e2e8f0" stroke-width="1"></line>
                    <polygon points="{{ $areaDiaria }}" fill="#0284c7" fill-opacity="0.12"></polygon>
                    <polyline points="{{ $lineaDiaria }}" fill="none" stroke="#0284c7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    @foreach ($puntosDiarios->filter(fn ($punto, $indice) => $indice === 0 || $indice === $cantidadDias - 1 || $punto['dia'] % 5 === 0) as $punto)
                        <circle cx="{{ $punto['x'] }}" cy="{{ $punto['y'] }}" r="4" fill="#0284c7">
                            <title>Día {{ $punto['dia'] }}: {{ $punto['valor'] }} viajes</title>
                        </circle>
                        <text x="{{ $punto['x'] }}" y="162" text-anchor="middle" font-size="10" fill="#64748b">{{ $punto['dia'] }}</text>
                    @endforeach
                </svg>
            </div>
            <p class="text-xs text-slate-500">Cada punto muestra los viajes programados, retrasados o cancelados en esa fecha; los días sin viajes aparecen en cero.</p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="text-lg font-extrabold text-slate-950">Viajes por estado</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución de los estados de los viajes del periodo.</p>
            @if ($detalle['total_viajes'] > 0)
                <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row">
                    <div class="relative h-36 w-36 shrink-0">
                        <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-label="Distribución de viajes por estado">
                            <circle cx="21" cy="21" r="15.915" fill="none" stroke="#e2e8f0" stroke-width="6"></circle>
                            @foreach ($graficosEstados as $segmento)
                                <circle cx="21" cy="21" r="15.915" fill="none" stroke="{{ $segmento['color'] }}" stroke-width="6"
                                        stroke-dasharray="{{ $segmento['porcentaje'] }} {{ 100 - $segmento['porcentaje'] }}"
                                        stroke-dashoffset="{{ -$segmento['desplazamiento'] }}"></circle>
                            @endforeach
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-extrabold text-slate-900">{{ $detalle['total_viajes'] }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($graficosEstados as $segmento)
                            <p class="flex items-center gap-2 text-xs text-slate-600">
                                <span class="inline-block h-2.5 w-2.5 rounded-sm" style="background-color: {{ $segmento['color'] }}"></span>
                                {{ $segmento['nombre'] }}: {{ $segmento['cantidad'] }} ({{ number_format($segmento['porcentaje'], 1, ',', '.') }}%)
                            </p>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">No hay viajes registrados para graficar en este periodo.</p>
            @endif
        </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h3 class="text-lg font-extrabold text-slate-950">Resumen por ruta</h3>
                <p class="mt-1 text-sm text-slate-500">Los viajes cancelados se muestran, pero no se incluyen en la ocupación.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">Ruta</th>
                            <th class="px-5 py-3">Viajes</th>
                            <th class="px-5 py-3">Boletos confirmados</th>
                            <th class="px-5 py-3">Capacidad</th>
                            <th class="px-5 py-3">Ocupación</th>
                            <th class="px-5 py-3">Cancelados</th>
                            <th class="px-5 py-3">Retrasados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detalle['por_ruta'] as $ruta)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $ruta['nombre'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $ruta['viajes'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $ruta['boletos_confirmados'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $ruta['capacidad'] }}</td>
                                @php
                                    $colorOcupacion = $ruta['ocupacion'] < 40
                                        ? 'text-rose-700'
                                        : ($ruta['ocupacion'] <= 70 ? 'text-amber-700' : 'text-emerald-700');
                                    $colorBarraOcupacion = $ruta['ocupacion'] < 40
                                        ? 'bg-rose-600'
                                        : ($ruta['ocupacion'] <= 70 ? 'bg-amber-500' : 'bg-emerald-600');
                                @endphp
                                <td class="min-w-40 px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2.5 min-w-16 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $colorBarraOcupacion }}" style="width: {{ min($ruta['ocupacion'], 100) }}%"></div>
                                        </div>
                                        <span class="font-semibold {{ $colorOcupacion }}">{{ number_format($ruta['ocupacion'], 1, ',', '.') }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $ruta['cancelados'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $ruta['retrasados'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">No hay viajes registrados para este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h3 class="text-lg font-extrabold text-slate-950">Detalle de viajes</h3>
                <p class="mt-1 text-sm text-slate-500">Fecha, salida, bus y estado de cada viaje del periodo.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">Fecha y salida</th>
                            <th class="px-5 py-3">Ruta</th>
                            <th class="px-5 py-3">Bus</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3">Boletos / capacidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detalle['viajes'] as $viaje)
                            <tr>
                                <td class="px-5 py-4 text-slate-600">{{ $viaje['fecha']->format('d/m/Y') }} · {{ \Illuminate\Support\Str::substr($viaje['hora'], 0, 5) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $viaje['ruta'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $viaje['bus'] }}</td>
                                <td class="px-5 py-4 capitalize text-slate-600">{{ $viaje['estado'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $viaje['boletos_confirmados'] }} / {{ $viaje['capacidad'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay viajes registrados para este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
