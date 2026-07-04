<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nombre del Rol" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            value="{{ old('name', $rol->name ?? '') }}" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    {{-- Permisos agrupados por módulo --}}
    <div>
        <x-input-label value="Permisos" />
        <div class="mt-2 grid grid-cols-2 gap-4">
            @foreach($permisos as $modulo => $lista)
                <div class="border rounded-lg p-4">
                    <p class="font-semibold text-gray-700 capitalize mb-2">{{ $modulo }}</p>
                    @foreach($lista as $permiso)
                        <label class="flex items-center gap-2 text-sm text-gray-600 mb-1">
                            <input type="checkbox" name="permisos[]"
                                   value="{{ $permiso->name }}"
                                   {{ in_array($permiso->name, $permisosActuales ?? []) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            {{ $permiso->name }}
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('permisos')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('seguridad.roles.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>