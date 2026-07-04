<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                value="{{ old('nombre', $conductor->nombre ?? '') }}" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="apellido" value="Apellido" />
            <x-text-input id="apellido" name="apellido" type="text" class="mt-1 block w-full"
                value="{{ old('apellido', $conductor->apellido ?? '') }}" />
            <x-input-error :messages="$errors->get('apellido')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="licencia" value="Número de licencia" />
            <x-text-input id="licencia" name="licencia" type="text" class="mt-1 block w-full"
                value="{{ old('licencia', $conductor->licencia ?? '') }}" />
            <x-input-error :messages="$errors->get('licencia')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                value="{{ old('telefono', $conductor->telefono ?? '') }}" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-1" />
        </div>
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.conductores.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>