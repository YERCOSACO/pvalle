<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Parametrización</p>
            <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">Agregar asiento de viaje</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="rounded-lg bg-white p-6 shadow">
            <form method="GET" action="{{ route('parametrizacion.asientoviaje.create') }}" class="mb-5 space-y-3">
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div>
                    <x-input-label for="bus_id" value="Bus" />
                    <select id="bus_id" name="bus_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">Seleccione un bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" @selected(old('bus_id', $busSeleccionado?->id) == $bus->id)>
                                {{ $bus->placa }} — {{ $bus->modelo ?? 'Sin modelo' }} ({{ $bus->capacidad }} asientos)
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>Ver viajes del bus</x-primary-button>
            </form>

            @if($busSeleccionado && $viajes->isEmpty())
                <div class="mb-5 flex items-center justify-between gap-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    <p>Este bus no tiene viajes activos para asociar el asiento.</p>
                    <a href="{{ route('parametrizacion.asientoviaje.gestion') }}" class="form-cancel">Cancelar</a>
                </div>
            @endif

            @if($busSeleccionado && $viajes->isNotEmpty())
            <form method="POST" action="{{ route('parametrizacion.asientoviaje.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                <div>
                    <x-input-label for="viaje_id" value="Viaje del bus seleccionado" />
                    <select id="viaje_id" name="viaje_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">Seleccione un viaje</option>
                        @foreach($viajes as $viaje)
                            <option value="{{ $viaje->id }}" @selected(old('viaje_id', $viajeSeleccionado) == $viaje->id)>
                                {{ $viaje->ruta?->nombre_ruta ?? 'Ruta no disponible' }} —
                                {{ $viaje->fecha_viaje?->format('d/m/Y') }} —
                                {{ $viaje->hora_salida }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('viaje_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="numero_asiento" value="Número de asiento" />
                    <x-text-input id="numero_asiento" name="numero_asiento" type="text" required maxlength="10"
                                  value="{{ old('numero_asiento') }}" placeholder="Ejemplo: 1A"
                                  class="mt-1 block w-full" />
                    <p class="mt-1 text-sm text-gray-500">El asiento debe estar dentro de la capacidad del bus seleccionado (número de fila y letra A-D).</p>
                    <x-input-error :messages="$errors->get('numero_asiento')" class="mt-1" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>Agregar asiento</x-primary-button>
                    <a href="{{ $returnTo === 'viaje' && $viajeSeleccionado ? route('parametrizacion.asientoviaje.viaje', $viajeSeleccionado) : route('parametrizacion.asientoviaje.gestion') }}"
                       class="form-cancel">Cancelar</a>
                </div>
            </form>
            @endif

            @if($buses->isEmpty())
                <p class="mt-5 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">No hay buses activos para agregar asientos.</p>
            @endif
        </div>
    </div>
</x-app-layout>