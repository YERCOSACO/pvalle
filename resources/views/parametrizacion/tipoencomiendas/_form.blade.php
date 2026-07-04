<div class="space-y-4">
    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
            value="{{ old('nombre', $tipoEncomienda->nombre ?? '') }}" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <textarea id="descripcion" name="descripcion" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('descripcion', $tipoEncomienda->descripcion ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="precio" value="Precio (Bs)" />
        <x-text-input id="precio" name="precio" type="number" step="0.01" class="mt-1 block w-full"
            value="{{ old('precio', $tipoEncomienda->precio ?? '') }}" />
        <x-input-error :messages="$errors->get('precio')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.tipoencomiendas.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>