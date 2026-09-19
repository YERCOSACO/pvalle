<div class="space-y-4" x-data="{ tipoAyudante: @js(old('tipo_ayudante', 'ninguno')) }">
    <div>
        <x-input-label for="viaje_id" value="Viaje" />
        <select name="viaje_id" id="viaje_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($viajes as $viaje)
                <option value="{{ $viaje->id }}" {{ old('viaje_id') == $viaje->id ? 'selected' : '' }}>
                    {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('viaje_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="conductor_principal" value="Conductor Principal *" />
        <select name="conductor_principal" id="conductor_principal"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($conductores as $conductor)
                <option value="{{ $conductor->id }}" {{ old('conductor_principal') == $conductor->id ? 'selected' : '' }}>
                    {{ $conductor->nombre_completo }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('conductor_principal')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="conductor_relevo" value="Conductor Relevo (opcional)" />
        <select name="conductor_relevo" id="conductor_relevo"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Ninguno --</option>
            @foreach($conductores as $conductor)
                <option value="{{ $conductor->id }}" {{ old('conductor_relevo') == $conductor->id ? 'selected' : '' }}>
                    {{ $conductor->nombre_completo }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('conductor_relevo')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="tipo_ayudante" value="Apoyo adicional (opcional)" />
        <select name="tipo_ayudante" id="tipo_ayudante" x-model="tipoAyudante"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="ninguno">-- Ninguno --</option>
            <option value="conductor">Seleccionar conductor registrado</option>
            <option value="nombre">Escribir nombre del ayudante</option>
        </select>
        <x-input-error :messages="$errors->get('tipo_ayudante')" class="mt-1" />
    </div>

    <div x-show="tipoAyudante === 'conductor'" x-cloak>
        <x-input-label for="ayudante" value="Conductor ayudante" />
        <select name="ayudante" id="ayudante"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Ninguno --</option>
            @foreach($conductores as $conductor)
                <option value="{{ $conductor->id }}" {{ old('ayudante') == $conductor->id ? 'selected' : '' }}>
                    {{ $conductor->nombre_completo }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('ayudante')" class="mt-1" />
    </div>

    <div x-show="tipoAyudante === 'nombre'" x-cloak>
        <x-input-label for="nombre_ayudante" value="Nombre del ayudante" />
        <input type="text" name="nombre_ayudante" id="nombre_ayudante"
               value="{{ old('nombre_ayudante') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
               placeholder="Escribe el nombre completo">
        <x-input-error :messages="$errors->get('nombre_ayudante')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.asignacionconductor.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>