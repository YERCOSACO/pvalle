<x-guest-layout>
    <p class="font-eyebrow text-xs font-bold uppercase tracking-[0.2em] text-[var(--mv-selva-600)]">Bienvenido de vuelta</p>
    <h1 class="font-display mt-1 text-2xl font-semibold text-[var(--mv-tinta-900)]">Inicia sesión</h1>

    <form method="POST" action="{{ route('cliente.login.store') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mv-input mt-2 block w-full rounded-lg px-3 py-2.5 text-sm">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-[var(--mv-tinta-900)]/20 text-[var(--mv-selva-600)] shadow-sm focus:ring-[var(--mv-selva-500)]">
                <span class="ms-2 text-sm text-[var(--mv-tinta-900)]/70">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-end">
            <button type="submit" class="mv-btn-primary rounded-xl px-5 py-2.5 text-sm font-bold transition">
                {{ __('Iniciar sesión') }}
            </button>
        </div>

        <div class="mt-6 text-center text-sm text-[var(--mv-tinta-900)]/60">
            <p>¿No eres cliente? <a href="{{ route('login') }}" class="font-semibold text-[var(--mv-selva-600)] hover:text-[var(--mv-bosque-900)]">Volver a menú</a>.</p>
        </div>
    </form>
</x-guest-layout>
