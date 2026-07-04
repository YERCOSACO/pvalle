<div class="space-y-4">
    <div>
        <x-input-label for="viaje_id" value="Viaje" />
        <select name="viaje_id" id="viaje_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($viajes as $viaje)
                <option value="{{ $viaje->id }}"
                    {{ old('viaje_id', $incidenciaviaje->viaje_id ?? '') == $viaje->id ? 'selected' : '' }}>
                    {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('viaje_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="tipo_incidencia_id" value="Tipo de Incidencia" />
        <select name="tipo_incidencia_id" id="tipo_incidencia_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($tiposIncidencia as $tipo)
                <option value="{{ $tipo->id }}"
                    {{ old('tipo_incidencia_id', $incidenciaviaje->tipo_incidencia_id ?? '') == $tipo->id ? 'selected' : '' }}>
                    {{ $tipo->nombre }} ({{ $tipo->nivel_gravedad }})
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400 mt-1">
            Leve: no afecta el viaje · Moderado: marca el viaje como "retrasado" · Grave: marca el viaje como "cancelado"
        </p>
        <x-input-error :messages="$errors->get('tipo_incidencia_id')" class="mt-1" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="fecha_inicio" value="Fecha y hora de inicio" />
            <x-text-input id="fecha_inicio" name="fecha_inicio" type="datetime-local" class="mt-1 block w-full"
                value="{{ old('fecha_inicio', isset($incidenciaviaje) ? $incidenciaviaje->fecha_inicio->format('Y-m-d\TH:i') : '') }}" />
            <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="fecha_fin" value="Fecha y hora de fin (opcional)" />
            <x-text-input id="fecha_fin" name="fecha_fin" type="datetime-local" class="mt-1 block w-full"
                value="{{ old('fecha_fin', isset($incidenciaviaje) && $incidenciaviaje->fecha_fin ? $incidenciaviaje->fecha_fin->format('Y-m-d\TH:i') : '') }}" />
            <p class="text-xs text-gray-400 mt-1">Ej: 1 hora para revisión, 1 día para derrumbe</p>
            <x-input-error :messages="$errors->get('fecha_fin')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="descripcion_detalle" value="Descripción del incidente" />
        <textarea id="descripcion_detalle" name="descripcion_detalle" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('descripcion_detalle', $incidenciaviaje->descripcion_detalle ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('descripcion_detalle')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.incidenciaviaje.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>