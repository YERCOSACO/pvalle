<div class="space-y-6" x-data="{
    viajeId: '',
    cantidadBoletos: 1,
    asientosSeleccionados: [],
    todosAsientos: [],
    asientosOcupados: [],
    metodoPago: 'QR',
    precio: '',
    cargando: false,

    toggleAsiento(asiento) {
        if (this.estaOcupado(asiento)) return;
        const idx = this.asientosSeleccionados.indexOf(asiento);
        if (idx >= 0) {
            this.asientosSeleccionados.splice(idx, 1);
        } else {
            if (this.asientosSeleccionados.length >= parseInt(this.cantidadBoletos)) {
                alert('Ya seleccionaste ' + this.cantidadBoletos + ' asiento(s). Es el máximo para esta reserva.');
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
            this.precio = '';
            this.asientosSeleccionados = [];
            return;
        }
        this.cargando = true;
        this.asientosSeleccionados = [];
        fetch(`/transaccional/boletos/asientos/${this.viajeId}`)
            .then(r => r.json())
            .then(data => {
                this.todosAsientos = data.todos;
                this.asientosOcupados = data.ocupados;
                this.precio = data.precio;
                this.cargando = false;
            });
    },

    chunk(arr, size) {
        const r = [];
        for (let i = 0; i < arr.length; i += size) r.push(arr.slice(i, i + size));
        return r;
    }
}">

    @if(!isset($boleto))

    {{-- Reserva --}}
    <div>
        <x-input-label for="reserva_id" value="Reserva" />
        <select name="reserva_id" id="reserva_id"
                @change="cantidadBoletos = $event.target.selectedOptions[0]?.dataset.cantidad ?? 1; asientosSeleccionados = [];"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($reservas as $reserva)
                <option value="{{ $reserva->id }}"
                        data-cantidad="{{ $reserva->cantidad }}"
                        {{ old('reserva_id') == $reserva->id ? 'selected' : '' }}>
                    {{ $reserva->cliente->nombre_completo }} — {{ $reserva->cantidad }} boleto(s)
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('reserva_id')" class="mt-1" />
    </div>

    {{-- Viaje --}}
    <div>
        <x-input-label for="viaje_id" value="Viaje" />
        <select name="viaje_id" id="viaje_id" x-model="viajeId" @change="cargarAsientos()"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar viaje --</option>
            @foreach($viajes as $viaje)
                <option value="{{ $viaje->id }}">
                    {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('viaje_id')" class="mt-1" />
    </div>

    {{-- Mapa de asientos --}}
    <div x-show="viajeId">
        <div class="flex items-center justify-between mb-2">
            <x-input-label value="Selecciona los asientos" />
            <span class="text-xs text-indigo-600 font-semibold"
                  x-text="asientosSeleccionados.length + ' de ' + cantidadBoletos + ' seleccionado(s)'">
            </span>
        </div>

        {{-- Inputs ocultos para enviar el array de asientos --}}
        <template x-for="(asiento, i) in asientosSeleccionados" :key="i">
            <input type="hidden" :name="'numero_asiento[' + i + ']'" :value="asiento">
        </template>

        <div x-show="cargando" class="text-gray-400 text-sm mt-2">Cargando asientos...</div>

        <div x-show="!cargando && todosAsientos.length > 0" class="mt-3">

            {{-- Leyenda --}}
            <div class="flex gap-6 text-xs text-gray-600 mb-4">
                <span class="flex items-center gap-1">
                    <span class="w-4 h-4 bg-emerald-500 rounded inline-block"></span> Disponible
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-4 h-4 bg-rose-500 rounded inline-block"></span> Ocupado
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-4 h-4 bg-indigo-600 rounded inline-block"></span> Seleccionado
                </span>
            </div>

            {{-- Bus --}}
            <div class="mx-auto w-fit bg-slate-50 border-4 border-slate-300 rounded-3xl p-5">
                <div class="h-10 bg-slate-700 rounded-t-2xl mb-4 relative flex items-center px-3">
                    <span class="text-slate-400 text-xs">🧑‍✈️</span>
                    <div class="absolute right-3 top-1.5 w-7 h-7 rounded-full border-4 border-slate-300 bg-white"></div>
                </div>

                <div class="space-y-2">
                    <template x-for="(fila, fi) in chunk(todosAsientos, 4)" :key="fi">
                        <div class="flex justify-center items-center gap-4">
                            <div class="flex gap-2">
                                <template x-for="asiento in fila.slice(0,2)" :key="asiento">
                                    <button type="button"
                                        @click="toggleAsiento(asiento)"
                                        :disabled="estaOcupado(asiento)"
                                        :class="{
                                            'bg-indigo-600 border-indigo-800 scale-110 ring-2 ring-indigo-300': estaSeleccionado(asiento),
                                            'bg-emerald-500 border-emerald-700 hover:scale-110 cursor-pointer': !estaOcupado(asiento) && !estaSeleccionado(asiento),
                                            'bg-rose-500 border-rose-700 cursor-not-allowed opacity-70': estaOcupado(asiento)
                                        }"
                                        class="w-11 h-11 rounded-lg border-b-4 text-white text-xs font-bold transition-all duration-150">
                                        <span x-text="asiento"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="w-4"></div>
                            <div class="flex gap-2">
                                <template x-for="asiento in fila.slice(2,4)" :key="asiento">
                                    <button type="button"
                                        @click="toggleAsiento(asiento)"
                                        :disabled="estaOcupado(asiento)"
                                        :class="{
                                            'bg-indigo-600 border-indigo-800 scale-110 ring-2 ring-indigo-300': estaSeleccionado(asiento),
                                            'bg-emerald-500 border-emerald-700 hover:scale-110 cursor-pointer': !estaOcupado(asiento) && !estaSeleccionado(asiento),
                                            'bg-rose-500 border-rose-700 cursor-not-allowed opacity-70': estaOcupado(asiento)
                                        }"
                                        class="w-11 h-11 rounded-lg border-b-4 text-white text-xs font-bold transition-all duration-150">
                                        <span x-text="asiento"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Asientos seleccionados visualmente --}}
            <div class="mt-3 flex flex-wrap gap-2 justify-center" x-show="asientosSeleccionados.length > 0">
                <template x-for="(a, i) in asientosSeleccionados" :key="i">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                        <span x-text="'Asiento ' + a"></span>
                        <button type="button" @click="asientosSeleccionados.splice(i, 1)"
                                class="text-indigo-400 hover:text-red-500 ml-1 font-bold">✕</button>
                    </span>
                </template>
            </div>
        </div>
    </div>

    {{-- Precio --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Precio por boleto:</span>
            <span class="font-medium" x-text="precio ? 'Bs ' + parseFloat(precio).toFixed(2) : '—'"></span>
        </div>
        <div class="flex justify-between text-sm mt-1">
            <span class="text-gray-600">Asientos seleccionados:</span>
            <span class="font-medium" x-text="asientosSeleccionados.length"></span>
        </div>
        <div class="flex justify-between font-bold text-indigo-700 mt-2 pt-2 border-t border-indigo-200">
            <span>Total a pagar:</span>
            <span x-text="precio ? 'Bs ' + (parseFloat(precio) * asientosSeleccionados.length).toFixed(2) : '—'"></span>
        </div>
        <input type="hidden" name="precio" :value="precio">
    </div>

    {{-- Método de pago --}}
    <div>
        <x-input-label for="metodo_pago" value="Método de pago" />
        <select name="metodo_pago" id="metodo_pago" x-model="metodoPago"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="QR">QR</option>
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
        </select>
        <div x-show="metodoPago === 'QR'"
             class="mt-2 bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-3 text-sm">
            Al elegir QR, el boleto queda en estado <strong>Pendiente</strong> por <strong>15 minutos</strong>.
            Si no se confirma el pago en ese tiempo, el asiento se libera automáticamente.
        </div>
    </div>

    <hr class="my-4">

    {{-- Datos de los pasajeros — uno por asiento seleccionado --}}
    <div x-show="asientosSeleccionados.length > 0">
        <p class="text-sm font-semibold text-gray-700 mb-3">
            Datos de los pasajeros
        </p>

        <template x-for="(asiento, i) in asientosSeleccionados" :key="i">
            <div class="border border-gray-200 rounded-lg p-4 mb-3" x-data="{ tipo: 'normal' }">
                <p class="text-xs font-bold text-indigo-600 mb-3"
                   x-text="'Pasajero ' + (i+1) + ' — Asiento ' + asiento">

                </p>

                {{-- Tipo de pasajero: normal / comodidad / mascota (define el recargo en el servidor) --}}
                <div class="mb-3 pb-3 border-b border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de pasajero
                    </label>
                    <select
                        :id="'tipo-'+i"
                        :name="'tipo_pasajero[' + i + ']'"
                        x-model="tipo"
                        class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="normal">Pasajero</option>
                        <option value="comodidad">Comodidad (espacio extra)</option>
                        <option value="mascota">Viaja con mascota</option>
                    </select>
                </div>

                {{-- Solo pide datos personales si es pasajero normal --}}
                <div class="grid grid-cols-3 gap-4" x-show="tipo === 'normal'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nombre completo
                        </label>

                        <input
                            :id="'nombre-'+i"
                            :name="'nombre_pasajero[' + i + ']'"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Cédula (CI)
                        </label>

                        <input
                            :id="'ci-'+i"
                            :name="'ci_pasajero[' + i + ']'"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Teléfono
                        </label>

                        <input
                            :id="'telefono-'+i"
                            :name="'telefono_pasajero[' + i + ']'"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>
            </div>
        </template>
    </div>

    @endif

    {{-- Edit mode: solo datos del pasajero --}}
    @isset($boleto)
    <div class="bg-gray-50 rounded-lg p-4 mb-4 grid grid-cols-3 gap-4 text-sm">
        <div>
            <span class="text-gray-500">Viaje</span>
            <p class="font-medium">{{ $boleto->viaje->ruta->nombre_ruta }}</p>
        </div>
        <div>
            <span class="text-gray-500">Asiento</span>
            <p class="font-bold text-indigo-600">{{ $boleto->numero_asiento }}</p>
        </div>
        <div>
            <span class="text-gray-500">Método de pago</span>
            <p class="font-medium">{{ $boleto->metodo_pago }}</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <x-input-label for="nombre_pasajero" value="Nombre completo" />
            <x-text-input id="nombre_pasajero" name="nombre_pasajero" type="text" class="mt-1 block w-full"
                value="{{ old('nombre_pasajero', $boleto->nombre_pasajero) }}" />
        </div>
        <div>
            <x-input-label for="ci_pasajero" value="Cédula (CI)" />
            <x-text-input id="ci_pasajero" name="ci_pasajero" type="text" class="mt-1 block w-full"
                value="{{ old('ci_pasajero', $boleto->ci_pasajero) }}" />
        </div>
        <div>
            <x-input-label for="telefono_pasajero" value="Teléfono" />
            <x-text-input id="telefono_pasajero" name="telefono_pasajero" type="text" class="mt-1 block w-full"
                value="{{ old('telefono_pasajero', $boleto->telefono_pasajero) }}" />
        </div>
    </div>

    {{-- Tipo de pasajero: normal / comodidad / mascota --}}
    @php
        $tipoActual = old('tipo_pasajero', $boleto->mascota ? 'mascota' : ($boleto->espacio_extra ? 'comodidad' : 'normal'));
    @endphp
    <div class="mt-4">
        <x-input-label for="tipo_pasajero" value="Tipo de pasajero" />
        <select name="tipo_pasajero" id="tipo_pasajero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="normal" {{ $tipoActual == 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="comodidad" {{ $tipoActual == 'comodidad' ? 'selected' : '' }}>Comodidad (espacio extra)</option>
            <option value="mascota" {{ $tipoActual == 'mascota' ? 'selected' : '' }}>Viaja con mascota</option>
        </select>
    </div>

    <div class="mt-4">
        <x-input-label for="estado" value="Estado" />
        <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="pendiente" {{ old('estado', $boleto->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="confirmado" {{ old('estado', $boleto->estado) == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
            <option value="cancelado" {{ old('estado', $boleto->estado) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>
    </div>
    @endisset

    <div class="flex items-center gap-4 pt-4">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.boletos.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>