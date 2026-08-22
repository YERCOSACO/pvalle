<x-guest-layout>

    <!-- Estado de sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            Iniciar sesión
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Ingresa a tu cuenta para continuar
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />

            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="ejemplo@correo.com"
            />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Ingresa tu contraseña"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <!-- Recordarme -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    name="remember"
                >

                <span class="ms-2 text-sm text-gray-600">
                    Recordarme
                </span>
            </label>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-between mt-6">

            @if (Route::has('password.request'))
                <a
                    class="text-sm text-indigo-600 hover:text-indigo-900 underline"
                    href="{{ route('password.request') }}"
                >
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Iniciar sesión
            </x-primary-button>

        </div>

        <!-- Registro de cliente -->
        <div class="mt-6 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">
                ¿Aún no tienes una cuenta?
            </p>

            <a
                href="{{ route('register') }}"
                class="mt-2 inline-block font-medium text-indigo-600 hover:text-indigo-900"
            >
                Regístrate como cliente
            </a>
        </div>

    </form>

</x-guest-layout>