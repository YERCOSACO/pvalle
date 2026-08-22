<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Mis Reservas</h2>
            <p class="mt-2 text-sm text-slate-600">Aquí puedes ver tus reservas activas.</p>
        </div>

        <div class="grid gap-6">
            @forelse($reservas as $reserva)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Reserva #{{ $reserva->id }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $reserva->cantidad }} boleto(s)</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ ucfirst($reserva->estado) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-slate-600 space-y-1">
                        <p>Total a pagar: S/ {{ number_format($reserva->total_pagar, 2) }}</p>
                        <p>Boletos registrados: {{ $reserva->boletos->count() }} / {{ $reserva->cantidad }}</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('cliente.boletos.create', $reserva) }}" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Seleccionar asientos
                        </a>
                        <a href="{{ route('cliente.boletos.qr', $reserva) }}" class="inline-flex items-center rounded-full border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">
                            Ver QR de pago
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-slate-500">
                    No tienes reservas activas.
                </div>
            @endforelse
        </div>
    </div>
</x-cliente-layout>
