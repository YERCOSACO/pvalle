<div class="space-y-4">
    <div>
        <x-input-label for="cliente_id" value="Cliente" />
        <select name="cliente_id" id="cliente_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('cliente_id', $reserva->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->nombre_completo }} — CI: {{ $cliente->cedula }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('cliente_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="cantidad" value="Cantidad de boletos a reservar" />
        <x-text-input id="cantidad" name="cantidad" type="number" min="1" class="mt-1 block w-full"
            value="{{ old('cantidad', $reserva->cantidad ?? 1) }}" />
        <p class="text-xs text-gray-400 mt-1">Después podrás registrar cada boleto con su asiento y pasajero</p>
        <x-input-error :messages="$errors->get('cantidad')" class="mt-1" />
    </div>

    @isset($reserva)
    <div>
        <x-input-label for="estado" value="Estado" />
        <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="pendiente" {{ old('estado', $reserva->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="confirmada" {{ old('estado', $reserva->estado) == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
            <option value="cancelada" {{ old('estado', $reserva->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
        </select>
    </div>
    @endisset

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.reservas.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>