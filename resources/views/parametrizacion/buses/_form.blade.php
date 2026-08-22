<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="placa" value="Placa" />
            <x-text-input id="placa" name="placa" type="text" class="mt-1 block w-full"
                value="{{ old('placa', $bus->placa ?? '') }}" />
            <x-input-error :messages="$errors->get('placa')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="capacidad" value="Capacidad de pasajeros" />
            <x-text-input id="capacidad" name="capacidad" type="number" class="mt-1 block w-full"
                value="{{ old('capacidad', $bus->capacidad ?? '') }}" />
            <x-input-error :messages="$errors->get('capacidad')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="modelo" value="Modelo" />
            <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full"
                value="{{ old('modelo', $bus->modelo ?? '') }}" />
            <x-input-error :messages="$errors->get('modelo')" class="mt-1" />
        </div>

    <div>
    <x-input-label for="tipo_bus" value="Tipo de bus" />
    <select name="tipo_bus" id="tipo_bus"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        <option value="">-- Seleccionar --</option>
        <option value="Semi-cama" {{ old('tipo_bus', $bus->tipo_bus ?? '') == 'Semi-cama' ? 'selected' : '' }}>
            Semi-cama
        </option>
        <option value="Cama" {{ old('tipo_bus', $bus->tipo_bus ?? '') == 'Cama' ? 'selected' : '' }}>
            Cama
        </option>
        <option value="Ejecutivo" {{ old('tipo_bus', $bus->tipo_bus ?? '') == 'Ejecutivo' ? 'selected' : '' }}>
            Ejecutivo
        </option>
    </select>
    <x-input-error :messages="$errors->get('tipo_bus')" class="mt-1" />
    </div>
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.buses.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>