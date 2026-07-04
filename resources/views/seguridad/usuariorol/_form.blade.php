<div class="space-y-4">

    @if(!isset($usuario))
    {{-- Solo en CREATE: elegir usuario --}}
    <div>
        <x-input-label for="usuario_id" value="Usuario" />
        <select name="usuario_id" id="usuario_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Seleccionar usuario</option>
            @foreach($usuarios as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('usuario_id')" class="mt-1" />
    </div>
    @else
    {{-- Solo en EDIT: mostrar usuario fijo --}}
    <div>
        <x-input-label value="Usuario" />
        <p class="mt-1 font-medium text-gray-800">{{ $usuario->name }} ({{ $usuario->email }})</p>
    </div>
    @endif

    <div>
        <x-input-label for="rol" value="Rol" />
        <select name="rol" id="rol"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar rol --</option>
            @foreach($roles as $rol)
                <option value="{{ $rol->name }}"
                    {{ old('rol', $rolActual ?? '') == $rol->name ? 'selected' : '' }}>
                    {{ $rol->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('rol')" class="mt-1" />
    </div>

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('seguridad.usuariorol.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>