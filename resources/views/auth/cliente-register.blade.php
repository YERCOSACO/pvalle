<x-guest-layout>
    <form method="POST" action="{{ route('cliente.register.store') }}">
        @csrf

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="apellido" value="Apellido" />
            <x-text-input id="apellido" class="block mt-1 w-full" type="text" name="apellido" :value="old('apellido')" required />
            <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="cedula" value="Cédula" />
            <x-text-input id="cedula" class="block mt-1 w-full" type="text" name="cedula" :value="old('cedula')" required />
            <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono')" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="direccion" value="Dirección" />
            <x-text-input id="direccion" class="block mt-1 w-full" type="text" name="direccion" :value="old('direccion')" />
            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
            <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" />
            <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena" value="Contraseña" />
            <x-text-input id="contrasena" class="block mt-1 w-full" type="password" name="contrasena" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('contrasena')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena_confirmation" value="Confirmar contraseña" />
            <x-text-input id="contrasena_confirmation" class="block mt-1 w-full" type="password" name="contrasena_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('contrasena_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('cliente.login') }}">¿Ya tienes cuenta? Inicia sesión</a>

            <x-primary-button>
                Crear cuenta
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
