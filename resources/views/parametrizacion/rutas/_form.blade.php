<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="origen" value="Origen" />
            <x-text-input id="origen" name="origen" type="text" class="mt-1 block w-full"
                value="{{ old('origen', $ruta->origen ?? '') }}" />
            <x-input-error :messages="$errors->get('origen')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="destino" value="Destino" />
            <x-text-input id="destino" name="destino" type="text" class="mt-1 block w-full"
                value="{{ old('destino', $ruta->destino ?? '') }}" />
            <x-input-error :messages="$errors->get('destino')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="distancia_km" value="Distancia (km)" />
            <x-text-input id="distancia_km" name="distancia_km" type="number" step="0.01" class="mt-1 block w-full"
                value="{{ old('distancia_km', $ruta->distancia_km ?? '') }}" />
            <x-input-error :messages="$errors->get('distancia_km')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="precio_base" value="Precio base (Bs)" />
            <x-text-input id="precio_base" name="precio_base" type="number" step="0.01" class="mt-1 block w-full"
                value="{{ old('precio_base', $ruta->precio_base ?? '') }}" />
            <x-input-error :messages="$errors->get('precio_base')" class="mt-1" />
        </div>
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.rutas.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>