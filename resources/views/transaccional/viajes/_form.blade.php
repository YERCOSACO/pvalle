<div class="form-stack">
    <div class="form-grid">
        <div>
            <x-input-label for="ruta_id" value="Ruta" />
            <select name="ruta_id" id="ruta_id" class="form-input mt-1 block w-full">
                <option value="">-- Seleccionar --</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}"
                        {{ old('ruta_id', $viaje->ruta_id ?? '') == $ruta->id ? 'selected' : '' }}>
                        {{ $ruta->nombre_ruta }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('ruta_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="bus_id" value="Bus" />
            <select name="bus_id" id="bus_id" class="form-input mt-1 block w-full">
                <option value="">-- Seleccionar --</option>
                @foreach($buses as $bus)
                    <option value="{{ $bus->id }}"
                        {{ old('bus_id', $viaje->bus_id ?? '') == $bus->id ? 'selected' : '' }}>
                        {{ $bus->placa }} ({{ $bus->capacidad }} asientos)
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('bus_id')" class="mt-1" />
        </div>
    </div>

    <div class="form-grid">
        <div>
            <x-input-label for="fecha_viaje" value="Fecha del viaje" />
            <x-text-input id="fecha_viaje" name="fecha_viaje" type="date" class="mt-1 block w-full"
                value="{{ old('fecha_viaje', isset($viaje) ? $viaje->fecha_viaje->format('Y-m-d') : '') }}" />
            <x-input-error :messages="$errors->get('fecha_viaje')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="hora_salida" value="Hora de salida" />
            <x-text-input id="hora_salida" name="hora_salida" type="time" class="mt-1 block w-full"
                value="{{ old('hora_salida', $viaje->hora_salida ?? '') }}" />
            <x-input-error :messages="$errors->get('hora_salida')" class="mt-1" />
        </div>
    </div>

    @isset($viaje)
    <div>
        <x-input-label for="estado" value="Estado del viaje" />
        <select name="estado" id="estado" class="form-input mt-1 block w-full">
            <option value="programado" {{ old('estado', $viaje->estado) == 'programado' ? 'selected' : '' }}>Programado</option>
            <option value="en curso" {{ old('estado', $viaje->estado) == 'en curso' ? 'selected' : '' }}>En curso</option>
            <option value="finalizado" {{ old('estado', $viaje->estado) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
            <option value="cancelado" {{ old('estado', $viaje->estado) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>
    </div>
    @endisset

    <div class="form-actions">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.viajes.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>
