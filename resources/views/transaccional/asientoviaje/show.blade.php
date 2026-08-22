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
                <a href="{{ route('transaccional.boletos.create') }}"
                   class="btn-primary">
                    + Nuevo Boleto
                </a>
                <a href="{{ route('transaccional.boletos.index') }}"
                   class="btn-secondary">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- Resumen --}}
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

            <div class="flex gap-6 items-start">

                {{-- Mapa grande --}}
                <div class="bg-white rounded-2xl shadow-lg p-8 flex-shrink-0">

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
                            <span class="text-slate-300 text-sm">🧑‍✈️</span>
                            <div class="absolute right-4 top-2 w-8 h-8 rounded-full border-4 border-slate-300 bg-white"></div>
                        </div>

                        @php $chunks = array_chunk($todos, 4); @endphp

                        <div class="space-y-4">
                            @foreach($chunks as $fila)
                                <div class="flex justify-center items-end gap-6">

                                    <div class="flex gap-3">
                                        @foreach(array_slice($fila, 0, 2) as $numero)
                                            @php
                                                $b = $boletos[$numero] ?? null;
                                                if (!$b) {
                                                    $color = 'bg-emerald-500 border-emerald-700';
                                                } elseif ($b->estado === 'confirmado') {
                                                    $color = 'bg-rose-500 border-rose-700';
                                                } else {
                                                    $color = 'bg-yellow-400 border-yellow-600';
                                                }
                                            @endphp
                                            <div class="flex flex-col items-center">
                                                @if($b)
                                                    <span class="text-xs text-gray-600 mb-1 max-w-[60px] truncate text-center font-medium">
                                                        {{ explode(' ', $b->nombre_pasajero)[0] }}
                                                    </span>
                                                    @if($b->estado === 'pendiente')
                                                        <span class="text-xs text-yellow-600 font-bold mb-1">⏱ {{ $b->minutos_restantes }}m</span>
                                                    @endif
                                                    <a href="{{ route('transaccional.boletos.edit', $b) }}"
                                                       class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md hover:scale-105 transition-transform"
                                                       title="{{ $b->nombre_pasajero }}">
                                                        {{ $numero }}
                                                    </a>
                                                @else
                                                    <span class="text-xs text-transparent mb-1">—</span>
                                                    <span class="text-xs text-transparent mb-1">—</span>
                                                    <div class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md">
                                                        {{ $numero }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="w-6"></div>

                                    <div class="flex gap-3">
                                        @foreach(array_slice($fila, 2, 2) as $numero)
                                            @php
                                                $b = $boletos[$numero] ?? null;
                                                if (!$b) {
                                                    $color = 'bg-emerald-500 border-emerald-700';
                                                } elseif ($b->estado === 'confirmado') {
                                                    $color = 'bg-rose-500 border-rose-700';
                                                } else {
                                                    $color = 'bg-yellow-400 border-yellow-600';
                                                }
                                            @endphp
                                            <div class="flex flex-col items-center">
                                                @if($b)
                                                    <span class="text-xs text-gray-600 mb-1 max-w-[60px] truncate text-center font-medium">
                                                        {{ explode(' ', $b->nombre_pasajero)[0] }}
                                                    </span>
                                                    @if($b->estado === 'pendiente')
                                                        <span class="text-xs text-yellow-600 font-bold mb-1">⏱ {{ $b->minutos_restantes }}m</span>
                                                    @endif
                                                    <a href="{{ route('transaccional.boletos.edit', $b) }}"
                                                       class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md hover:scale-105 transition-transform"
                                                       title="{{ $b->nombre_pasajero }}">
                                                        {{ $numero }}
                                                    </a>
                                                @else
                                                    <span class="text-xs text-transparent mb-1">—</span>
                                                    <span class="text-xs text-transparent mb-1">—</span>
                                                    <div class="w-14 h-14 {{ $color }} border-b-4 rounded-xl text-white text-xs font-bold flex items-center justify-center shadow-md">
                                                        {{ $numero }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Lista de pasajeros --}}
                <div class="flex-1 bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-700 text-lg">Lista de Pasajeros</h3>
                        <span class="text-xs text-gray-500">
                            Registrado por: <strong>{{ auth()->user()->name }}</strong>
                            ({{ auth()->user()->getRoleNames()->first() }})
                        </span>
                    </div>

                    @if($boletos->isEmpty())
                        <div class="text-center py-12 text-gray-400">
                            <p class="text-4xl mb-3">🪑</p>
                            <p class="text-sm">Sin pasajeros en este viaje.</p>
                            <a href="{{ route('transaccional.boletos.create') }}"
                               class="mt-4 btn-primary">
                                + Registrar primer boleto
                            </a>
                        </div>
                    @else
                        <div class="space-y-2 max-h-[580px] overflow-y-auto pr-1">
                            @foreach($boletos as $numero => $b)
                                <div class="flex items-center justify-between border border-gray-100 rounded-lg p-3 hover:bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <span class="bg-indigo-100 text-indigo-700 font-bold text-xs px-2 py-1 rounded min-w-[40px] text-center">
                                            {{ $numero }}
                                        </span>
                                        <div>
                                            <p class="font-medium text-sm text-gray-800">{{ $b->nombre_pasajero }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                CI: {{ $b->ci_pasajero ?? '—' }}
                                                @if($b->telefono_pasajero) · 📞 {{ $b->telefono_pasajero }} @endif
                                                @if($b->espacio_extra) · 🪑 @endif
                                                @if($b->mascota) · 🐾 @endif
                                            </p>
                                            @if($b->estado === 'pendiente' && $b->expira_en)
                                                <p class="text-xs text-yellow-600 font-semibold mt-0.5">
                                                    ⏱ Expira en {{ $b->minutos_restantes }} min
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        @php
                                            $colores = ['confirmado' => 'bg-green-100 text-green-700', 'pendiente' => 'bg-yellow-100 text-yellow-700'];
                                        @endphp
                                        <span class="{{ $colores[$b->estado] ?? 'bg-gray-100 text-gray-600' }} text-xs px-2 py-1 rounded-full">
                                            {{ ucfirst($b->estado) }}
                                        </span>

                                        @if($b->estado === 'pendiente' && !$b->esta_expirado)
                                            <form method="POST" action="{{ route('transaccional.boletos.confirmar-pago', $b) }}">
                                                @csrf @method('PATCH')
                                                <button class="btn-success btn-sm">✓</button>
                                            </form>
                                        @endif

                                        <a href="{{ route('transaccional.boletos.edit', $b) }}"
                                           class="btn-warning btn-sm">✏️</a>

                                        <a href="{{ route('transaccional.boletos.imprimir', $b) }}"
                                           target="_blank"
                                           class="btn-neutral btn-sm">🖨️</a>

                                        <form method="POST" action="{{ route('transaccional.boletos.destroy', $b) }}"
                                              onsubmit="return confirm('¿Eliminar boleto de {{ $b->nombre_pasajero }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger btn-sm">🗑️</button>
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