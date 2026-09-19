<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#1B6B45]">Mis viajes</p>
            <h2 class="mt-2 text-2xl font-semibold text-[#12241C]">Mis reservas por viaje</h2>
            <p class="mt-2 text-sm text-[#12241C]/60">Consulta todas las reservas y boletos asociados a cada salida.</p>
        </div>

        @forelse($reservasPorViaje as $reservasViaje)
            @php
                $viaje = $reservasViaje->first()->boletos->first()->viaje;
                $boletosViaje = $reservasViaje->flatMap->boletos;
            @endphp
            <section class="overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                <div class="bg-[#0B2A1E] p-6 text-white">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#9BE28C]">Viaje</p>
                            <h3 class="mt-2 text-xl font-semibold">{{ $viaje->ruta->nombre_ruta ?? 'Ruta no disponible' }}</h3>
                            <p class="mt-2 text-sm text-slate-300">{{ optional($viaje->fecha_viaje)->format('d/m/Y') }} · {{ substr($viaje->hora_salida, 0, 5) }} · {{ $boletosViaje->count() }} boleto(s)</p>
                        </div>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-[#9BE28C]">{{ $reservasViaje->count() }} reserva(s)</span>
                    </div>
                </div>

                <div class="divide-y divide-black/5">
                    @foreach($reservasViaje as $reserva)
                        @php
                            $boletosActivos = $reserva->boletos->where('estado_base', 1)->where('estado', '!=', 'cancelado');
                        @endphp
                        <div class="p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-[#12241C]/50">Reserva #{{ $reserva->id }}</p>
                                    <p class="mt-1 font-semibold text-[#12241C]">{{ $boletosActivos->count() }} / {{ $reserva->cantidad }} boleto(s) registrados</p>
                                </div>
                                <span class="rounded-full bg-[#F6F3E9] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#12241C]/70">{{ ucfirst($reserva->estado) }}</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($boletosActivos as $boleto)
                                    <span class="rounded-lg bg-[#1B6B45]/10 px-3 py-2 text-sm font-semibold text-[#1B6B45]">Asiento {{ $boleto->numero_asiento }}</span>
                                @endforeach
                            </div>
                            <a href="{{ route('cliente.boletos.qr', $reserva) }}" class="mt-4 inline-flex items-center rounded-xl border border-[#1B6B45] px-4 py-2 text-sm font-semibold text-[#1B6B45] hover:bg-[#1B6B45]/5">Ver QR de pago</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            @if($reservasSinViaje->isEmpty())
                <div class="rounded-2xl border border-black/10 bg-white p-6 text-[#12241C]/50 shadow-sm">No tienes reservas activas.</div>
            @endif
        @endforelse

        @if($reservasSinViaje->isNotEmpty())
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <h3 class="font-semibold text-amber-900">Reservas pendientes de viaje</h3>
                <p class="mt-1 text-sm text-amber-800">Estas reservas todavía no tienen asientos ni viaje asignado.</p>
                <div class="mt-4 space-y-3">
                    @foreach($reservasSinViaje as $reserva)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white/70 p-4">
                            <span class="text-sm font-semibold text-[#12241C]/80">Reserva #{{ $reserva->id }} · {{ $reserva->cantidad }} boleto(s)</span>
                            <a href="{{ route('cliente.boletos.create', $reserva) }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">Elegir viaje y asientos →</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-cliente-layout>
