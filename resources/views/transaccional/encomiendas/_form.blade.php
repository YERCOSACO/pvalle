<div class="space-y-4" x-data="{
    remitenteEsCliente: {{ old('cliente_id', $encomienda->cliente_id ?? '') ? 'true' : 'false' }},
    precios: {{ $tiposEncomienda->pluck('precio', 'id')->toJson() }},
    tipoSeleccionado: '{{ old('tipo_encomienda_id', $encomienda->tipo_encomienda_id ?? '') }}',
    cantidad: {{ old('cantidad', $encomienda->cantidad ?? 1) }},
    get precioUnitario() { return this.tipoSeleccionado ? parseFloat(this.precios[this.tipoSeleccionado]) : 0 },
    get total() { return (this.precioUnitario * this.cantidad).toFixed(2) }
}">

    <div class="flex items-center gap-3 mb-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="remitenteEsCliente" class="rounded border-gray-300">
            El remitente tiene cuenta de Cliente
        </label>
    </div>

    <div x-show="remitenteEsCliente">
        <x-input-label for="cliente_id" value="Cliente (remitente)" />
        <select name="cliente_id" id="cliente_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('cliente_id', $encomienda->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->nombre_completo }} — CI: {{ $cliente->cedula }}
                </option>
            @endforeach
        </select>
    </div>

    <div x-show="!remitenteEsCliente" class="grid grid-cols-3 gap-4">
        <div>
            <x-input-label for="remitente_nombre" value="Nombre del remitente" />
            <x-text-input id="remitente_nombre" name="remitente_nombre" type="text" class="mt-1 block w-full"
                value="{{ old('remitente_nombre', $encomienda->remitente_nombre ?? '') }}" />
            <x-input-error :messages="$errors->get('remitente_nombre')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="remitente_ci" value="CI del remitente" />
            <x-text-input id="remitente_ci" name="remitente_ci" type="text" class="mt-1 block w-full"
                value="{{ old('remitente_ci', $encomienda->remitente_ci ?? '') }}" />
        </div>
        <div>
            <x-input-label for="remitente_telefono" value="Teléfono del remitente" />
            <x-text-input id="remitente_telefono" name="remitente_telefono" type="text" class="mt-1 block w-full"
                value="{{ old('remitente_telefono', $encomienda->remitente_telefono ?? '') }}" />
        </div>
    </div>

    <hr class="my-4">

    <div class="grid grid-cols-3 gap-4">
        <div>
            <x-input-label for="destinatario_nombre" value="Nombre del destinatario" />
            <x-text-input id="destinatario_nombre" name="destinatario_nombre" type="text" class="mt-1 block w-full"
                value="{{ old('destinatario_nombre', $encomienda->destinatario_nombre ?? '') }}" />
            <x-input-error :messages="$errors->get('destinatario_nombre')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="destinatario_ci" value="CI del destinatario" />
            <x-text-input id="destinatario_ci" name="destinatario_ci" type="text" class="mt-1 block w-full"
                value="{{ old('destinatario_ci', $encomienda->destinatario_ci ?? '') }}" />
        </div>
        <div>
            <x-input-label for="destinatario_telefono" value="Teléfono del destinatario" />
            <x-text-input id="destinatario_telefono" name="destinatario_telefono" type="text" class="mt-1 block w-full"
                value="{{ old('destinatario_telefono', $encomienda->destinatario_telefono ?? '') }}" />
        </div>
    </div>

    <div>
        <x-input-label for="viaje_id" value="Viaje" />
        <select name="viaje_id" id="viaje_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($viajes as $viaje)
                <option value="{{ $viaje->id }}"
                    {{ old('viaje_id', $encomienda->viaje_id ?? '') == $viaje->id ? 'selected' : '' }}>
                    {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('viaje_id')" class="mt-1" />
    </div>

    <hr class="my-4">

    {{-- Tipo de encomienda con precio visible al lado --}}
    <div class="grid grid-cols-3 gap-4 items-end">
        <div class="col-span-2">
            <x-input-label for="tipo_encomienda_id" value="Tipo de Encomienda" />
            <select name="tipo_encomienda_id" id="tipo_encomienda_id" x-model="tipoSeleccionado"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Seleccionar --</option>
                @foreach($tiposEncomienda as $tipo)
                    <option value="{{ $tipo->id }}">
                        {{ $tipo->nombre }} — Bs {{ number_format($tipo->precio, 2) }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('tipo_encomienda_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="cantidad" value="Cantidad" />
            <input type="number" id="cantidad" name="cantidad" x-model="cantidad" min="1"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <x-input-error :messages="$errors->get('cantidad')" class="mt-1" />
        </div>
    </div>

    {{-- Precio unitario y total calculados en vivo --}}
    <div class="bg-gray-50 rounded-lg p-4 flex justify-between text-sm">
        <span class="text-gray-600">
            Precio unitario: <span class="font-medium" x-text="'Bs ' + precioUnitario.toFixed(2)"></span>
        </span>
        <span class="text-gray-800 font-semibold">
            Total a pagar: <span x-text="'Bs ' + total"></span>
        </span>
    </div>

    @isset($encomienda)
    <div>
        <x-input-label for="estado" value="Estado" />
        <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="recibido" {{ old('estado', $encomienda->estado) == 'recibido' ? 'selected' : '' }}>Recibido</option>
            <option value="en transito" {{ old('estado', $encomienda->estado) == 'en transito' ? 'selected' : '' }}>En tránsito</option>
            <option value="entregado" {{ old('estado', $encomienda->estado) == 'entregado' ? 'selected' : '' }}>Entregado</option>
            <option value="cancelado" {{ old('estado', $encomienda->estado) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>
    </div>
    @endisset

    <div class="flex items-center gap-4 pt-4">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.encomiendas.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>