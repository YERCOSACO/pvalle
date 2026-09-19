<x-guest-layout>
    <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.2em] text-[var(--mv-selva-600)]">Únete a Mi Valle</p>
    <h1 class="font-display mt-1 text-2xl font-semibold text-[var(--mv-tinta-900)]">Crea tu cuenta</h1>

    <form method="POST" action="{{ route('cliente.register.store') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="apellido" value="Apellido" />
            <input id="apellido" type="text" name="apellido" value="{{ old('apellido') }}" required
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="cedula" value="Cédula" />
            <input id="cedula" type="text" name="cedula" value="{{ old('cedula') }}" required
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="telefono" value="Teléfono" />
            <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="direccion" value="Dirección" />
            <input id="direccion" type="text" name="direccion" value="{{ old('direccion') }}"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena" value="Contraseña" />
            <input id="contrasena" type="password" name="contrasena" required autocomplete="new-password"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('contrasena')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contrasena_confirmation" value="Confirmar contraseña" />
            <input id="contrasena_confirmation" type="password" name="contrasena_confirmation" required autocomplete="new-password"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('contrasena_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-between">
            <a class="text-sm font-semibold text-[var(--mv-selva-600)] hover:text-[var(--mv-bosque-900)]" href="{{ route('cliente.login') }}">¿Ya tienes cuenta? Inicia sesión</a>

            <button type="submit" class="mv-btn-primary rounded-xl px-5 py-2.5 text-sm font-bold transition">
                Crear cuenta
            </button>
        </div>
    </form>
</x-guest-layout>
