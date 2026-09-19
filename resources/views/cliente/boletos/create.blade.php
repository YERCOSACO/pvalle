<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-[#12241C]">Selecciona tus asientos</h2>
            <p class="mt-2 text-sm text-[#12241C]/60">Elige los asientos para tu reserva y genera el QR de pago.</p>
        </div>

        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('cliente.boletos.store') }}" x-data="{
                viajeId: '{{ old('viaje_id', request('viaje_id', '')) }}',
                cantidadBoletos: {{ $reserva->cantidad }},
                asientosSeleccionados: [],
                tiposPasajero: {},
                todosAsientos: [],
                asientosOcupados: [],
                precio: 0,
                cargando: false,
                toggleAsiento(asiento) {
                    if (this.estaOcupado(asiento)) return;
                    const idx = this.asientosSeleccionados.indexOf(asiento);
                    if (idx >= 0) {
                        this.asientosSeleccionados.splice(idx, 1);
                        delete this.tiposPasajero[asiento];
                    } else {
                        if (this.asientosSeleccionados.length >= this.cantidadBoletos) {
                            alert('Ya seleccionaste ' + this.cantidadBoletos + ' asiento(s).');
                            return;
                        }
                        this.asientosSeleccionados.push(asiento);
                        this.tiposPasajero[asiento] = 'normal';
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
                },
                filasAsientos() {
                    if (this.todosAsientos.length <= 5) return [this.todosAsientos];
                    const filas = [this.todosAsientos.slice(0, 2)];
                    const centro = this.todosAsientos.slice(2, -5);
                    for (let i = 0; i < centro.length; i += 4) filas.push(centro.slice(i, i + 4));
                    filas.push(this.todosAsientos.slice(-5));
                    return filas;
                }
            }">
                @csrf
                <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">

                <div class="mb-4">
                    <x-input-label for="viaje_id" value="Viaje" />
                    <select id="viaje_id" name="viaje_id" x-model="viajeId" @change="cargarAsientos()"
                            class="mt-1 block w-full rounded-lg border-black/15 bg-[#F6F3E9] shadow-sm focus:border-[#238453] focus:ring-[#238453]">
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
                        <p class="font-semibold text-[#12241C]/80">Selecciona los asientos</p>
                        <span class="text-sm text-[#12241C]/50" x-text="asientosSeleccionados.length + ' / ' + cantidadBoletos"></span>
                    </div>

                    <div x-show="cargando" class="text-sm text-[#12241C]/50">Cargando asientos...</div>

                    <div x-show="!cargando && todosAsientos.length === 0" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        No hay asientos disponibles para mostrar en este momento.
                    </div>

                    <div x-show="!cargando && todosAsientos.length > 0" class="space-y-3">
                        <div class="flex gap-4 text-xs text-[#12241C]/60">
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#6FCF64]"></span>Disponible</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-rose-500"></span>Ocupado</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#D9A441]"></span>Seleccionado</span>
                        </div>

                        <div class="mx-auto w-fit bg-[#F6F3E9] border-4 border-[#EEE9D8] rounded-3xl p-5">
                            <div class="h-10 bg-[#0B2A1E] rounded-t-2xl mb-4 relative flex items-center px-3">
                                <span class="text-[#9BE28C] text-xs font-semibold uppercase tracking-wider">Cabina</span>
                                <div class="absolute right-3 top-1.5 w-7 h-7 rounded-full border-4 border-[#EEE9D8] bg-white"></div>
                            </div>
                            <template x-for="(fila, fi) in filasAsientos()" :key="fi">
                                <div class="grid grid-cols-5 gap-x-3 w-fit mx-auto">
                                    <template x-for="(asiento, ai) in fila" :key="asiento">
                                        <button type="button"
                                            :style="'grid-column: ' + ((fi === 0 || fi === filasAsientos().length - 1) ? ai + 1 : [1, 2, 4, 5][ai])"
                                            @click="toggleAsiento(asiento)"
                                            :disabled="estaOcupado(asiento)"
                                            :class="{
                                                'bg-[#D9A441] border-[#B5842B] text-white': estaSeleccionado(asiento),
                                                'bg-[#6FCF64] border-[#3FA341] text-[#0B2A1E]': !estaOcupado(asiento) && !estaSeleccionado(asiento),
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

                <template x-for="(asiento, index) in asientosSeleccionados" :key="asiento">
                    <input type="hidden" :name="'numero_asiento['+index+']'" :value="asiento">
                </template>

                <div class="mb-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-[#12241C]/80">Tipo de pasajero por asiento</p>
                    </div>
                    <div class="mt-3 space-y-3">
                        <template x-for="(asiento, index) in asientosSeleccionados" :key="asiento">
                            <div class="rounded-xl border border-black/10 bg-[#F6F3E9] p-3">
                                <div class="flex items-center gap-3">
                                    <span class="min-w-[90px] font-semibold text-[#12241C]">Asiento <span x-text="asiento"></span></span>
                                    <select class="mt-1 block w-full rounded-lg border-black/15 bg-white shadow-sm focus:border-[#238453] focus:ring-[#238453]"
                                            :name="'tipo_pasajero['+index+']'"
                                            x-model="tiposPasajero[asiento]">
                                        <option value="normal">Pasajero normal</option>
                                        <option value="comodidad">Comodidad (espacio extra)</option>
                                        <option value="mascota">Viaja con mascota</option>
                                    </select>
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3" x-show="tiposPasajero[asiento] === 'normal'">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#12241C]/70">Nombre del pasajero</label>
                                        <input type="text"
                                               :name="'nombre_pasajero['+index+']'"
                                               value="{{ auth('cliente')->user()->nombre_completo ?? '' }}"
                                               class="mt-1 block w-full rounded-lg border-black/15 bg-white shadow-sm focus:border-[#238453] focus:ring-[#238453]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#12241C]/70">Cédula</label>
                                        <input type="text"
                                               :name="'ci_pasajero['+index+']'"
                                               value="{{ auth('cliente')->user()->cedula ?? '' }}"
                                               class="mt-1 block w-full rounded-lg border-black/15 bg-white shadow-sm focus:border-[#238453] focus:ring-[#238453]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#12241C]/70">Teléfono</label>
                                        <input type="text"
                                               :name="'telefono_pasajero['+index+']'"
                                               value="{{ auth('cliente')->user()->telefono ?? '' }}"
                                               class="mt-1 block w-full rounded-lg border-black/15 bg-white shadow-sm focus:border-[#238453] focus:ring-[#238453]">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mb-4 bg-[#F6F3E9] border border-[#1B6B45]/20 rounded-lg p-4">
                    <p class="text-sm text-[#12241C]/60">Precio por boleto:</p>
                    <p class="text-lg font-semibold text-[#12241C]" x-text="precio ? 'Bs ' + parseFloat(precio).toFixed(2) : '—'"></p>
                    <p class="text-sm text-[#12241C]/60 mt-1">Total aproximado:</p>
                    <p class="text-lg font-semibold text-[#12241C]" x-text="precio ? 'Bs ' + (parseFloat(precio) * asientosSeleccionados.length).toFixed(2) : '—'"></p>
                </div>

                <input type="hidden" name="metodo_pago" value="QR">
                <div class="mb-4">
                    <div class="flex items-center justify-between rounded-lg border border-[#1B6B45]/20 bg-[#F6F3E9] px-4 py-3">
                        <span class="text-sm font-semibold text-[#12241C]/80">Método de pago</span>
                        <span class="rounded-full bg-[#1B6B45] px-4 py-1 text-sm font-bold text-white">QR</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('cliente.reservas.index') }}" class="inline-flex items-center rounded-full border border-black/15 bg-white px-4 py-2 text-sm font-semibold text-[#12241C]/70 hover:bg-[#F6F3E9]">
                        Volver
                    </a>
                    <button type="submit" x-bind:disabled="asientosSeleccionados.length !== cantidadBoletos || cargando"
                            class="inline-flex items-center rounded-full bg-[#6FCF64] px-5 py-2.5 text-sm font-bold text-[#0B2A1E] transition hover:bg-[#9BE28C] disabled:cursor-not-allowed disabled:opacity-50">
                        Continuar al pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-cliente-layout>
