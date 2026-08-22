<div class="form-stack">
    <div class="form-grid">
        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                value="{{ old('nombre', $cliente->nombre ?? '') }}" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="apellido" value="Apellido" />
            <x-text-input id="apellido" name="apellido" type="text" class="mt-1 block w-full"
                value="{{ old('apellido', $cliente->apellido ?? '') }}" />
            <x-input-error :messages="$errors->get('apellido')" class="mt-1" />
        </div>
    </div>

    <div class="form-grid">
        <div>
            <x-input-label for="cedula" value="Cédula" />
            <x-text-input id="cedula" name="cedula" type="text" class="mt-1 block w-full"
                value="{{ old('cedula', $cliente->cedula ?? '') }}" />
            <x-input-error :messages="$errors->get('cedula')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                value="{{ old('email', $cliente->email ?? '') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>

    <div class="form-grid">
        <div>
            <x-input-label for="contrasena" value="Contraseña" />
            <x-text-input id="contrasena" name="contrasena" type="password" class="mt-1 block w-full" />
            @isset($cliente)
                <p class="form-hint">Dejar vacío para mantener la contraseña actual.</p>
            @endisset
            <x-input-error :messages="$errors->get('contrasena')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="contrasena_confirmation" value="Confirmar contraseña" />
            <x-text-input id="contrasena_confirmation" name="contrasena_confirmation"
                type="password" class="mt-1 block w-full" />
        </div>
    </div>

    <div class="form-grid">
        <div>
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                value="{{ old('telefono', $cliente->telefono ?? '') }}" />
        </div>

        <div>
            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
            <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1 block w-full"
                value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento ?? '') }}" />
        </div>
    </div>

    <div>
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
            value="{{ old('direccion', $cliente->direccion ?? '') }}" />
    </div>

    <div class="form-actions">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('parametrizacion.clientes.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>
