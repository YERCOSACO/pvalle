<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $viaje->ruta->nombre_ruta }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    📅 {{ $viaje->fecha_viaje->format('d/m/Y') }} &nbsp;
                    🕒 {{ $viaje->hora_salida }} &nbsp;
                    🚌 {{ $viaje->bus->placa }} &nbsp;
                    💺 {{ $viaje->bus->capacidad }} asientos
                </p>
            </div>
            <div class="flex gap-3">
                <form method="GET" action="{{ route('transaccional.boletos.por-viaje', $viaje) }}">
                    <label for="cambiar-viaje" class="sr-only">Seleccionar viaje disponible</label>
                    <select id="cambiar-viaje"
                            class="border-gray-300 rounded-lg text-sm shadow-sm"
                            onchange="if (this.value) window.location.href = this.value">
                        <option value="">Seleccionar viaje disponible</option>
                        @foreach($viajesDisponibles as $viajeDisponible)
                            <option value="{{ route('transaccional.boletos.por-viaje', $viajeDisponible) }}"
                                {{ $viajeDisponible->id === $viaje->id ? 'selected' : '' }}>
                                {{ $viajeDisponible->ruta->nombre_ruta }} · {{ $viajeDisponible->fecha_viaje->format('d/m/Y') }} · {{ $viajeDisponible->hora_salida }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('transaccional.boletos.create') }}"
                   class="btn-primary">
                    + Nuevo Boleto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-3xl font-bold text-gray-800">{{ $totalAsientos }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total asientos</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-3xl font-bold text-emerald-600">{{ $totalDisponibles }}</p>
                    <p class="text-xs text-gray-500 mt-1">Disponibles</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-3xl font-bold text-rose-600">{{ $totalOcupados }}</p>
                    <p class="text-xs text-gray-500 mt-1">Ocupados</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600">{{ $porcentaje }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Ocupación</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <div class="bg-white rounded-2xl shadow-lg p-8 flex-shrink-0 w-full lg:w-auto">
                    <div class="flex justify-center gap-6 mb-6 text-xs text-gray-600">
                        <span class="flex items-center gap-2">
                            <span class="w-4 h-4 bg-emerald-500 rounded"></span> Disponible
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="w-4 h-4 bg-rose-500 rounded"></span> Confirmado
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="w-4 h-4 bg-yellow-400 rounded"></span> Pendiente QR
                        </span>
                    </div>

                    <div class="mx-auto w-fit bg-slate-50 border-4 border-slate-300 rounded-3xl p-6">
                        <div class="h-12 bg-slate-700 rounded-t-2xl mb-6 relative flex items-center px-4">
                            <span class="text-slate-300 text-sm">BUS (DELANTERO)</span>
                            <div class="absolute right-4 top-2 w-8 h-8 rounded-full border-4 border-slate-300 bg-white"></div>
                        </div>

                        @php
                            $filasMapa = [];
                            $restantes = $todos;

                            if (count($restantes) > 5) {
                                $filasMapa[] = array_splice($restantes, 0, 2);
                                $ultimaFila = array_splice($restantes, -5);
                                foreach (array_chunk($restantes, 4) as $fila) {
                                    $filasMapa[] = $fila;
                                }
                                $filasMapa[] = $ultimaFila;
                            } else {
                                $filasMapa = array_chunk($restantes, 4);
                            }
                        @endphp

                        <div class="space-y-3">
                            @foreach($filasMapa as $indiceFila => $fila)
                                @php
                                    $esPrimeraFila = $indiceFila === 0;
                                    $esUltimaFila = $indiceFila === count($filasMapa) - 1;
                                @endphp
                                <div class="grid grid-cols-5 gap-x-3 items-start w-fit mx-auto">
                                    @foreach($fila as $indiceAsiento => $numero)
                                        @php
                                            $columna = $esPrimeraFila || $esUltimaFila
                                                ? $indiceAsiento + 1
                                                : [1, 2, 4, 5][$indiceAsiento];
                                                $claveAsiento = strtoupper(trim((string) $numero));
                                                $b = $boletos[$claveAsiento] ?? null;
                                                $estadoAsiento = $estadosAsientos[$claveAsiento] ?? 'disponible';

                                                if ($b?->estado === 'confirmado') {
                                                    $color = 'bg-rose-500 border-rose-700';
                                                } elseif ($b?->estado === 'pendiente') {
                                                    $color = 'bg-yellow-400 border-yellow-600';
                                                } elseif (in_array($estadoAsiento, ['ocupado', 'bloqueado'], true)) {
                                                    $color = 'bg-rose-500 border-rose-700';
                                                } else {
                                                    $color = 'bg-emerald-500 border-emerald-700';
                                                }
                                            @endphp
                                            <div class="flex flex-col items-center w-16" style="grid-column: {{ $columna }};">
                                                <span class="text-[10px] text-gray-600 leading-tight h-3 max-w-[64px] truncate text-center font-medium">
                                                    {{ $b ? explode(' ', $b->nombre_pasajero)[0] : '' }}
                                                </span>
                                                <span class="text-[10px] text-yellow-600 font-bold leading-tight h-3 mb-1">
                                                    {{ ($b && $b->estado === 'pendiente') ? '⏱ '.$b->minutos_restantes.'m' : '' }}
                                                </span>
                                                @if($b)
                                                    <a href="{{ route('transaccional.boletos.edit', $b) }}"
                                                       class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md hover:scale-105 transition-transform"
                                                       title="{{ $b->nombre_pasajero }}">
                                                        {{ $numero }}
                                                    </a>
                                                @else
                                                    <div class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md">
                                                        {{ $numero }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex-1 w-full bg-white rounded-2xl shadow-lg p-6 min-w-0">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-700 text-lg">Lista de Pasajeros</h3>
                        <span class="text-xs text-gray-500">
                            Registrado por: <strong>{{ auth()->user()->name }}</strong>
                            ({{ auth()->user()->getRoleNames()->first() }})
                        </span>
                    </div>

                    @if($boletos->isEmpty())
                        <div class="text-center py-12 text-gray-400">

                        </div>
                    @else
                        <div class="flex flex-col gap-2 max-h-[580px] overflow-y-auto pr-1">
                            @foreach($boletos as $numero => $b)
                                <div class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border border-gray-100 rounded-lg p-3 hover:bg-gray-50">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <span class="bg-indigo-100 text-indigo-700 font-bold text-xs px-2 py-1 rounded min-w-[40px] text-center flex-shrink-0">
                                            {{ $numero }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-sm text-gray-800 truncate">{{ $b->nombre_pasajero }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                CI: {{ $b->ci_pasajero ?? '—' }}
                                                @if($b->telefono_pasajero) · 📞 {{ $b->telefono_pasajero }} @endif
                                            </p>
                                            <div class="flex gap-1 mt-1 flex-wrap">
                                                @if($b->espacio_extra)
                                                    <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-1 rounded-full font-semibold">ESPACIO EXTRA</span>
                                                @endif
                                                @if($b->mascota)
                                                    <span class="bg-amber-100 text-amber-700 text-[10px] px-2 py-1 rounded-full font-semibold">MASCOTA</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 flex-shrink-0 flex-wrap">
                                        @php
                                            $colores = ['confirmado' => 'bg-green-100 text-green-700', 'pendiente' => 'bg-yellow-100 text-yellow-700'];
                                        @endphp
                                        <span class="{{ $colores[$b->estado] ?? 'bg-gray-100 text-gray-600' }} text-xs px-2 py-1 rounded-full">
                                            {{ $b->esta_expirado ? 'Expirado' : ucfirst($b->estado) }}
                                        </span>
                                        @if($b->estado === 'pendiente' && !$b->esta_expirado)
                                            <form method="POST" action="{{ route('transaccional.boletos.confirmar-pago', $b) }}">
                                                @csrf @method('PATCH')
                                                <button class="btn-success btn-sm">✓</button>
                                            </form>
                                        @endif
                                        @if($b->comprobante_url)
                                            <a href="{{ $b->comprobante_url }}" target="_blank" class="btn-primary btn-sm">📎 Comprobante</a>
                                        @endif
                                        <a href="{{ route('transaccional.boletos.edit', $b) }}" class="btn-warning btn-sm">Modificar</a>
                                        <a href="{{ route('transaccional.boletos.imprimir', $b) }}" target="_blank" class="btn-success btn-sm">Imprimir</a>
                                        <form method="POST" action="{{ route('transaccional.boletos.destroy', $b) }}" onsubmit="return confirm('¿Eliminar boleto de {{ $b->nombre_pasajero }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t flex justify-between text-sm text-gray-600">
                            <span>{{ $boletos->count() }} pasajero(s)</span>
                            <span class="font-bold text-indigo-600">
                                Total: Bs {{ number_format($boletos->sum('precio'), 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
