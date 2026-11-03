<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Parametrización · Asientos del viaje</p>
                <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">Bus {{ $bus->placa }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $bus->modelo ?? 'Modelo no especificado' }} · {{ $bus->capacidad }} asientos de capacidad
                </p>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $viaje->ruta?->nombre_ruta ?? 'Ruta no disponible' }} ·
                    {{ $viaje->fecha_viaje?->format('d/m/Y') }} ·
                    {{ $viaje->hora_salida ? substr($viaje->hora_salida, 0, 5) : '' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('parametrizacion.asientoviaje.gestion') }}" class="btn-secondary">Volver a buses</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4">
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

        <div class="flex flex-row items-stretch gap-4 overflow-x-auto">
            <section class="w-fit flex-shrink-0 rounded-2xl bg-white p-4 shadow-lg sm:p-8">
                <div class="mb-5 flex flex-wrap gap-3 text-xs text-slate-600">
                    <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-emerald-500"></span>Disponible</span>
                    <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-rose-500"></span>Ocupado</span>
                    <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-amber-400"></span>Faltante o pendiente</span>
                    <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-slate-400"></span>Bloqueado</span>

                </div>

                <div class="mx-auto w-fit max-w-full rounded-[2rem] border-4 border-slate-300 bg-slate-50 p-4 sm:p-6">
                    <div class="mb-6 flex h-12 items-center justify-between rounded-t-2xl bg-slate-700 px-4 text-sm text-white">
                        <span>Frente del bus</span><span>Conductor</span>
                    </div>

                    @php
                        $filasMaqueta = [];
                        $asientosRestantes = $asientosEsperados;

                        if (count($asientosRestantes) > 5) {
                            $filasMaqueta[] = array_splice($asientosRestantes, 0, 2);
                            $ultimaFila = array_splice($asientosRestantes, -5);
                            foreach (array_chunk($asientosRestantes, 4) as $fila) {
                                $filasMaqueta[] = $fila;
                            }
                            $filasMaqueta[] = $ultimaFila;
                        } else {
                            $filasMaqueta = array_chunk($asientosRestantes, 4);
                        }
                    @endphp

                    <div class="space-y-3">
                        @foreach($filasMaqueta as $indiceFila => $fila)
                            @php
                                $esPrimeraFila = $indiceFila === 0;
                                $esUltimaFila = $indiceFila === count($filasMaqueta) - 1;
                            @endphp
                            <div class="grid grid-cols-5 items-start gap-x-3">
                                @foreach($fila as $indice => $numero)
                                    @php
                                        $asiento = $asientosRegistrados->get($numero);
                                        $boleto = $boletos->get($numero);
                                        $estado = ! $asiento ? 'faltante' : ($asiento->estado === 'bloqueado' ? 'bloqueado' : ($boleto?->estado ?? $asiento->estado));
                                        $columna = $esPrimeraFila || $esUltimaFila
                                            ? $indice + 1
                                            : [1, 2, 4, 5][$indice];
                                        $colorAsiento = match($estado) {
                                            'confirmado', 'ocupado' => 'border-rose-700 bg-rose-500',
                                            'pendiente' => 'border-amber-600 bg-amber-400',
                                            'bloqueado' => 'border-slate-500 bg-slate-400',
                                            'faltante' => 'border-amber-500 bg-amber-50 text-amber-800',
                                            default => 'border-emerald-700 bg-emerald-500',
                                        };
                                        $nombreEstado = match($estado) {
                                            'confirmado' => 'Ocupado',
                                            'pendiente' => 'Pendiente',
                                            'disponible' => 'Disponible',
                                            'ocupado' => 'Ocupado',
                                            'bloqueado' => 'Bloqueado',
                                            'faltante' => 'Faltante',
                                            default => 'Maqueta',
                                        };
                                    @endphp
                                    <div class="flex w-12 flex-col items-center text-center sm:w-14" style="grid-column: {{ $columna }};">
                                        @if($boleto)
                                            <span class="mb-1 h-3 max-w-full truncate text-[10px] font-medium leading-tight text-slate-600">
                                                {{ explode(' ', $boleto->nombre_pasajero)[0] }}
                                            </span>
                                        @else
                                            <span class="mb-1 h-3 text-[10px] text-transparent">—</span>
                                        @endif
                                        @if($asiento && auth()->user()->can('asientoviaje.editar'))
                                            <a href="{{ route('parametrizacion.asientoviaje.edit', ['asientoviaje' => $asiento, 'return_to' => 'viaje']) }}"
                                               title="{{ $numero }} · {{ $nombreEstado }}{{ $boleto ? ' · ' . $boleto->nombre_pasajero : '' }}"
                                               class="flex h-11 w-11 items-center justify-center rounded-lg border-b-4 text-xs font-bold shadow transition hover:scale-105 sm:h-12 sm:w-12 {{ $colorAsiento }} {{ $estado === 'faltante' ? 'border-dashed' : '' }}">
                                                {{ $numero }}
                                            </a>
                                        @else
                                            <div title="{{ $numero }} · {{ $nombreEstado }}{{ $boleto ? ' · ' . $boleto->nombre_pasajero : '' }}"
                                                 class="flex h-11 w-11 items-center justify-center rounded-lg border-b-4 text-xs font-bold shadow sm:h-12 sm:w-12 {{ $colorAsiento }} {{ $estado === 'faltante' ? 'border-dashed' : '' }}">
                                                {{ $numero }}
                                            </div>
                                        @endif
                                        <p class="mt-1 text-[0.6rem] leading-tight text-slate-500">{{ $nombreEstado }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="mt-4 text-center text-sm text-slate-500">La distribución coincide con el mapa de Boleto: dos asientos a cada lado del pasillo central.</p>
            </section>

            <aside class="min-h-0 min-w-[18rem] flex flex-1 flex-col gap-4">
                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="font-semibold text-slate-800">Asientos registrados</h3>
                        <p class="mt-1 text-xs text-slate-500">Modifica el estado o da de baja un asiento. Los que tienen boleto vigente están protegidos.</p>
                        <div class="scrollbar-subtle mt-3 max-h-[220px] space-y-2 overflow-y-auto pr-1">
                            @foreach($asientosRegistrados as $asiento)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                                    <span class="font-semibold text-slate-700">
                                        {{ $asiento->numero_asiento }}
                                        <span class="block text-xs font-normal text-slate-500">{{ ucfirst($asiento->estado) }}</span>
                                    </span>
                                    <div class="flex gap-2">
                                        @can('asientoviaje.editar')
                                            <a href="{{ route('parametrizacion.asientoviaje.edit', ['asientoviaje' => $asiento, 'return_to' => 'viaje']) }}"
                                               class="btn-warning btn-sm">Modificar</a>
                                        @endcan
                                        @can('asientoviaje.eliminar')
                                            <form method="POST" action="{{ route('parametrizacion.asientoviaje.destroy', $asiento) }}"
                                                  onsubmit="return confirm('¿Dar de baja este asiento?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_to" value="viaje">
                                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                            @if($asientosRegistrados->isEmpty())
                                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Este viaje todavía no tiene asientos registrados.</p>
                            @endif
                        </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
