<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Selecciona tus asientos</h2>
            <p class="mt-2 text-sm text-slate-600">Elige los asientos para tu reserva y genera el QR de pago.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('cliente.boletos.store') }}" x-data="{
                viajeId: '{{ old('viaje_id', '') }}',
                cantidadBoletos: {{ $reserva->cantidad }},
                asientosSeleccionados: [],
                todosAsientos: [],
                asientosOcupados: [],
                precio: 0,
                cargando: false,
                toggleAsiento(asiento) {
                    if (this.estaOcupado(asiento)) return;
                    const idx = this.asientosSeleccionados.indexOf(asiento);
                    if (idx >= 0) {
                        this.asientosSeleccionados.splice(idx, 1);
                    } else {
                        if (this.asientosSeleccionados.length >= this.cantidadBoletos) {
                            alert('Ya seleccionaste ' + this.cantidadBoletos + ' asiento(s).');
                            return;
                        }
                        this.asientosSeleccionados.push(asiento);
                    }
                },
                estaSeleccionado(asiento) {
                    return this.asientosSeleccionados.includes(asiento);
                },
                estaOcupado(asiento) {
                    return this.asientosOcupados.includes(asiento);
                },
                cargarAsientos() {
                    if (!this.viajeId) {
                        this.todosAsientos = [];
                        this.asientosOcupados = [];
                        this.precio = 0;
                        this.asientosSeleccionados = [];
                        return;
                    }
                    this.cargando = true;
                    this.asientosSeleccionados = [];
                    fetch(`/cliente/boletos/asientos/${this.viajeId}`)
                        .then(r => {
                            if (!r.ok) throw new Error('No se pudieron cargar los asientos.');
                            return r.json();
                        })
                        .then(data => {
                            this.todosAsientos = data.todos;
                            this.asientosOcupados = data.ocupados;
                            this.precio = data.precio;
                            this.cargando = false;
                        })
                        .catch(() => {
                            this.todosAsientos = [];
                            this.asientosOcupados = [];
                            this.precio = 0;
                            this.cargando = false;
                            alert('No se pudieron cargar los asientos. Actualiza la página e inténtalo de nuevo.');
                        });
                },
                chunk(arr, size) {
                    const r = [];
                    for (let i = 0; i < arr.length; i += size) r.push(arr.slice(i, i + size));
                    return r;
                }
            }">
                @csrf
                <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">

                <div class="mb-4">
                    <x-input-label for="viaje_id" value="Viaje" />
                    <select id="viaje_id" name="viaje_id" x-model="viajeId" @change="cargarAsientos()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-- Selecciona viaje --</option>
                        @foreach($viajes as $viaje)
                            <option value="{{ $viaje->id }}">
                                {{ $viaje->ruta->nombre_ruta }} — {{ optional($viaje->fecha_viaje)->format('d/m/Y') }} {{ $viaje->hora_salida }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('viaje_id')" class="mt-2" />
                </div>

                <div x-show="viajeId" class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-slate-700">Selecciona los asientos</p>
                        <span class="text-sm text-slate-500" x-text="asientosSeleccionados.length + ' / ' + cantidadBoletos"></span>
                    </div>

                    <div x-show="cargando" class="text-sm text-gray-500">Cargando asientos...</div>

                    <div x-show="!cargando && todosAsientos.length === 0" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        No hay asientos disponibles para mostrar en este momento.
                    </div>

                    <div x-show="!cargando && todosAsientos.length > 0" class="space-y-3">
                        <div class="flex gap-4 text-xs text-gray-600">
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span>Disponible</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-rose-500"></span>Ocupado</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-indigo-600"></span>Seleccionado</span>
                        </div>

                        <div class="mx-auto w-fit bg-slate-50 border-4 border-slate-300 rounded-3xl p-5">
                            <div class="h-10 bg-slate-700 rounded-t-2xl mb-4 relative flex items-center px-3">
                                <span class="text-slate-400 text-xs">🧑‍✈️ Bus</span>
                                <div class="absolute right-3 top-1.5 w-7 h-7 rounded-full border-4 border-slate-300 bg-white"></div>
                            </div>
                            <template x-for="fila in chunk(todosAsientos, 4)" :key="fila[0]">
                                <div class="flex justify-center gap-3">
                                    <template x-for="asiento in fila" :key="asiento">
                                        <button type="button"
                                            @click="toggleAsiento(asiento)"
                                            :disabled="estaOcupado(asiento)"
                                            :class="{
                                                'bg-indigo-600 border-indigo-800 text-white': estaSeleccionado(asiento),
                                                'bg-emerald-500 border-emerald-700 text-white': !estaOcupado(asiento) && !estaSeleccionado(asiento),
                                                'bg-rose-500 border-rose-700 text-white opacity-70 cursor-not-allowed': estaOcupado(asiento)
                                            }"
                                            class="w-12 h-12 rounded-lg border-2 font-semibold transition">
                                            <span x-text="asiento"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <template x-for="(asiento, index) in asientosSeleccionados" :key="index">
                    <input type="hidden" :name="'numero_asiento['+index+']'" :value="asiento">
                </template>

                <div class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <p class="text-sm text-slate-600">Precio por boleto:</p>
                    <p class="text-lg font-semibold text-slate-900" x-text="precio ? 'Bs ' + parseFloat(precio).toFixed(2) : '—'"></p>
                    <p class="text-sm text-slate-600 mt-1">Total aproximado:</p>
                    <p class="text-lg font-semibold text-slate-900" x-text="precio ? 'Bs ' + (parseFloat(precio) * asientosSeleccionados.length).toFixed(2) : '—'"></p>
                </div>

                <div class="mb-4">
                    <x-input-label for="metodo_pago" value="Método de pago" />
                    <select id="metodo_pago" name="metodo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="QR">QR</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('cliente.reservas.index') }}" class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Volver
                    </a>
                    <x-primary-button x-bind:disabled="asientosSeleccionados.length !== cantidadBoletos || cargando">
                        Continuar al pago
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-cliente-layout>
