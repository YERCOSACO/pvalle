<div class="space-y-4">
    <div>
        <x-input-label value="Viaje" />
        <p class="mt-1 font-medium text-gray-800">
            {{ $asientoviaje->viaje->ruta->nombre_ruta }} — {{ $asientoviaje->viaje->fecha_viaje->format('d/m/Y') }}
        </p>
    </div>

    <div>
        <x-input-label value="Número de asiento" />
        <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $asientoviaje->numero_asiento }}</p>
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select name="estado" id="estado"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="disponible" {{ old('estado', $asientoviaje->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
            <option value="ocupado" {{ old('estado', $asientoviaje->estado) == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
            <option value="bloqueado" {{ old('estado', $asientoviaje->estado) == 'bloqueado' ? 'selected' : '' }}>Bloqueado</option>
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>Actualizar</x-primary-button>
        <a href="{{ route('parametrizacion.asientoviaje.show', $asientoviaje->viaje_id) }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>