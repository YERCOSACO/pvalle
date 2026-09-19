<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-[#12241C]">Mis Encomiendas</h2>
            <p class="mt-2 text-sm text-[#12241C]/60">Revisa el estado de tus paquetes y envíos.</p>
        </div>

        <div class="grid gap-6">
            @forelse($encomiendas as $encomienda)
                <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-[#12241C]/50">Encomienda #{{ $encomienda->id }}</p>
                            <p class="mt-2 text-lg font-semibold text-[#12241C]">{{ $encomienda->destinatario_nombre }}</p>
                        </div>
                        <span class="rounded-full bg-[#F6F3E9] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#1B6B45]">{{ ucfirst($encomienda->estado) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-[#12241C]/60">
                        <p>Viaje: {{ $encomienda->viaje->ruta->nombre_ruta ?? '—' }}</p>
                        <p>Destino: {{ $encomienda->viaje->ruta->destino ?? '—' }}</p>
                        <p>Total a pagar: Bs. {{ number_format($encomienda->total_pagar, 2) }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm text-[#12241C]/50">
                    No tienes encomiendas registradas.
                </div>
            @endforelse
        </div>
    </div>
</x-cliente-layout>
