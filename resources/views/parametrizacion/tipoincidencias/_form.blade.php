<div class="space-y-4">
    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
            value="{{ old('nombre', $tipoIncidencia->nombre ?? '') }}" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <textarea id="descripcion" name="descripcion" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('descripcion', $tipoIncidencia->descripcion ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="nivel_gravedad" value="Nivel de gravedad" />
        <select name="nivel_gravedad" id="nivel_gravedad"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">-- Seleccionar --</option>
            <option value="Leve" {{ old('nivel_gravedad', $tipoIncidencia->nivel_gravedad ?? '') == 'Leve' ? 'selected' : '' }}>Leve</option>
            <option value="Moderado" {{ old('nivel_gravedad', $tipoIncidencia->nivel_gravedad ?? '') == 'Moderado' ? 'selected' : '' }}>Moderado</option>
            <option value="Grave" {{ old('nivel_gravedad', $tipoIncidencia->nivel_gravedad ?? '') == 'Grave' ? 'selected' : '' }}>Grave</option>
        </select>
        <x-input-error :messages="$errors->get('nivel_gravedad')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.tipoincidencias.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>