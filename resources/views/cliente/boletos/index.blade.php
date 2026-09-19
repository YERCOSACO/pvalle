<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#1B6B45]">Mis viajes</p>
            <h2 class="mt-2 text-2xl font-semibold text-[#12241C]">Mis boletos por viaje</h2>
            <p class="mt-2 text-sm text-[#12241C]/60">Cada salida reúne tus reservas, asientos y estados de pago.</p>
        </div>

        @forelse($boletosPorViaje as $boletosViaje)
            @php $viaje = $boletosViaje->first()->viaje; @endphp
            <section class="overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                <div class="bg-[#0B2A1E] p-6 text-white">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#9BE28C]">Viaje</p>
                            <h3 class="mt-2 text-xl font-semibold">{{ $viaje->ruta->nombre_ruta ?? 'Ruta no disponible' }}</h3>
                            <p class="mt-2 text-sm text-slate-300">{{ optional($viaje->fecha_viaje)->format('d/m/Y') }} · {{ substr($viaje->hora_salida, 0, 5) }}</p>
                        </div>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-[#9BE28C]">{{ $boletosViaje->count() }} boleto(s)</span>
                    </div>
                </div>

                <div class="divide-y divide-black/5">
                    @foreach($boletosViaje->groupBy('reserva_id') as $reservaId => $boletosReserva)
                        <div class="p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-[#12241C]/50">Reserva #{{ $reservaId }}</p>
                                    <p class="mt-1 font-semibold text-[#12241C]">{{ $boletosReserva->count() }} boleto(s)</p>
                                </div>
                                <span class="rounded-full bg-[#F6F3E9] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#12241C]/70">Boleto(s) del cliente</span>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($boletosReserva as $boleto)
                                    @php
                                        $tipoPasajero = $boleto->mascota
                                            ? 'Viaje con mascota'
                                            : ($boleto->espacio_extra
                                                ? 'Comodidad (espacio extra)'
                                                : 'Pasajero normal');
                                    @endphp
                                    <div class="rounded-xl border border-black/5 bg-[#F6F3E9] p-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-[#12241C]">Asiento {{ $boleto->numero_asiento }}</span>
                                            <span class="rounded-full bg-white px-2 py-1 text-xs font-semibold text-[#12241C]/70">{{ ucfirst($boleto->estado) }}</span>
                                        </div>
                                        <div class="mt-3 space-y-2 text-sm text-[#12241C]/60">
                                            <p><span class="font-semibold text-[#12241C]">Tipo:</span> {{ $tipoPasajero }}</p>
                                            <p><span class="font-semibold text-[#12241C]">Pasajero:</span> {{ $boleto->nombre_pasajero }}</p>
                                            <p><span class="font-semibold text-[#12241C]">Precio:</span> Bs. {{ number_format($boleto->precio, 2) }}</p>
                                            <p><span class="font-semibold text-[#12241C]">Pago:</span> {{ ucfirst($boleto->metodo_pago) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-black/10 bg-white p-6 text-[#12241C]/50 shadow-sm">No tienes boletos registrados.</div>
        @endforelse
    </div>
</x-cliente-layout>
