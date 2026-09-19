<x-guest-layout>

    <!-- Estado de sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.2em] text-[var(--mv-selva-600)]">Bienvenido de vuelta</p>
        <h2 class="font-display mt-1 text-2xl font-semibold text-[var(--mv-tinta-900)]">
            Iniciar sesión
        </h2>
        <p class="mt-2 text-sm text-[var(--mv-tinta-900)]/60">
            Ingresa a tu cuenta para continuar
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />

            <input
                id="email"
                type="email"
                name="email"
                value=""
                required
                autofocus
                autocomplete="username"
                placeholder="ejemplo@correo.com"
                class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm"
            >

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Ingresa tu contraseña"
                class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm"
            >

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <!-- Recordarme -->
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-[var(--mv-tinta-900)]/20 text-[var(--mv-selva-600)] shadow-sm focus:ring-[var(--mv-selva-500)]"
                    name="remember"
                >

                <span class="ms-2 text-sm text-[var(--mv-tinta-900)]/70">
                    Recordarme
                </span>
            </label>
        </div>

        <!-- Acciones -->
        <div class="mt-6 flex items-center justify-between">

            @if (Route::has('password.request'))
                <a
                    class="text-sm font-semibold text-[var(--mv-selva-600)] underline hover:text-[var(--mv-bosque-900)]"
                    href="{{ route('password.request') }}"
                >
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <button type="submit" class="mv-btn-primary ms-3 rounded-xl px-5 py-2.5 text-sm font-bold transition">
                Iniciar sesión
            </button>

        </div>

        <!-- Registro de cliente -->
        <div class="mt-6 border-t border-[var(--mv-tinta-900)]/10 pt-6 text-center">
            <p class="text-sm text-[var(--mv-tinta-900)]/60">
                ¿Aún no tienes una cuenta?
            </p>

            <a
                href="{{ route('register') }}"
                class="mt-2 inline-block font-semibold text-[var(--mv-selva-600)] hover:text-[var(--mv-bosque-900)]"
            >
                Regístrate como cliente
            </a>
        </div>

    </form>

</x-guest-layout>