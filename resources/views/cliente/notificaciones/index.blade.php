<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Notificaciones</h2>
            <p class="mt-2 text-sm text-slate-600">Las últimas notificaciones relacionadas con tu cuenta.</p>
        </div>

        <div class="grid gap-6">
            @forelse($notificaciones as $notificacion)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ $notificacion->origen_legible }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $notificacion->titulo }}</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ optional($notificacion->fecha_envio)->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-600">{{ $notificacion->mensaje }}</p>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-slate-500">
                    No tienes notificaciones recientes.
                </div>
            @endforelse
        </div>
    </div>
</x-cliente-layout>
