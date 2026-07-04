<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nombre completo" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            value="{{ old('name', $usuario->name ?? '') }}" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            value="{{ old('email', $usuario->email ?? '') }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password" value="Contraseña" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation"
            type="password" class="mt-1 block w-full" />
    </div>


    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('seguridad.usuarios.index') }}"
           class="text-sm text-gray-500 hover:underline">Cancelar</a>
    </div>
</div>