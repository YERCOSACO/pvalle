<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Mis Boletos</h2>
            <p class="mt-2 text-sm text-slate-600">Consulta el estado y los detalles de tus boletos.</p>
        </div>

        <div class="grid gap-6">
            @forelse($boletos as $boleto)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Boleto #{{ $boleto->id }} — Asiento {{ $boleto->numero_asiento }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $boleto->viaje->ruta->nombre_ruta ?? 'Viaje' }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ ucfirst($boleto->estado) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-slate-600 space-y-1">
                        <p>Fecha de viaje: {{ optional($boleto->viaje->fecha_viaje)->format('d/m/Y') }}</p>
                        <p>Hora de salida: {{ $boleto->viaje->hora_salida }}</p>
                        <p>Precio: S/ {{ number_format($boleto->precio, 2) }}</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('cliente.boletos.qr', $boleto->reserva) }}" class="inline-flex items-center rounded-full border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">
                            Ver QR de pago
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-slate-500">
                    No tienes boletos registrados.
                </div>
            @endforelse
        </div>
    </div>
</x-cliente-layout>
