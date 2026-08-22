<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Mis Encomiendas</h2>
            <p class="mt-2 text-sm text-slate-600">Revisa el estado de tus paquetes y envíos.</p>
        </div>

        <div class="grid gap-6">
            @forelse($encomiendas as $encomienda)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Encomienda #{{ $encomienda->id }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $encomienda->destinatario_nombre }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ ucfirst($encomienda->estado) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        <p>Viaje: {{ $encomienda->viaje->ruta->nombre_ruta ?? '—' }}</p>
                        <p>Destino: {{ $encomienda->viaje->ruta->destino ?? '—' }}</p>
                        <p>Total a pagar: S/ {{ number_format($encomienda->total_pagar, 2) }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-slate-500">
                    No tienes encomiendas registradas.
                </div>
            @endforelse
        </div>
    </div>
</x-cliente-layout>
