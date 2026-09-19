<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-700">Cuenta</p>
            <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Mi perfil</h2>
        </div>
    </x-slot>

    <div class="space-y-6 py-8">
        <div class="profile-identity">
            <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-700">Usuario del sistema</p>
                <h1 class="mt-1 truncate text-2xl font-extrabold text-slate-950">{{ $user->name }}</h1>
                <p class="mt-1 truncate text-sm text-slate-500">{{ $user->email }}</p>
            </div>
            <div class="profile-role">
                {{ $user->getRoleNames()->first() ?? 'Sin rol asignado' }}
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="profile-card">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="profile-card">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="profile-danger-card">
            <div class="max-w-3xl">
                    @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
