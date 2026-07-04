<div class="space-y-4">
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
        <x-input-label for="conductor_relevo" value="Conductor Relevo *" />
        <select name="conductor_relevo" id="conductor_relevo"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($conductores as $conductor)
                <option value="{{ $conductor->id }}" {{ old('conductor_relevo') == $conductor->id ? 'selected' : '' }}>
                    {{ $conductor->nombre_completo }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('conductor_relevo')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ayudante" value="Ayudante (opcional)" />
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

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.asignacionconductor.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>