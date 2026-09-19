<x-cliente-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm text-center">
            <h2 class="text-xl font-semibold text-[#12241C]">QR de pago generado</h2>
            <p class="mt-2 text-sm text-[#12241C]/60">Escanea este QR para finalizar el pago de tu reserva.</p>
        </div>

        <div class="rounded-3xl border border-black/10 bg-white p-6 shadow-sm mx-auto max-w-md">
            <img src="{{ $qrImage }}" alt="QR de pago" class="mx-auto w-72 rounded-3xl border border-black/10 bg-white p-4" />
            <div class="mt-6 text-left text-sm text-[#12241C]/70 space-y-2">
                <p><strong>Reserva:</strong> #{{ $reserva->id }}</p>
                <p><strong>Cliente:</strong> {{ auth('cliente')->user()->nombre_completo }}</p>
                <p><strong>Boletos:</strong> {{ $reserva->boletos->count() }} / {{ $reserva->cantidad }}</p>
                <p><strong>Teléfono de pago:</strong> 71005452</p>
            </div>

            <div class="mt-6 rounded-2xl bg-[#F6F3E9] border border-[#1B6B45]/25 p-4 text-sm text-[#1B6B45]">
                Este QR es ficticio. Para este ejemplo, utiliza el número <strong>71005452</strong>.
            </div>

            <div class="mt-6 rounded-2xl border border-black/10 bg-[#F6F3E9] p-4 text-sm text-[#12241C]/70">
                Al enviar el comprobante, tus boletos quedan como <strong>Pendiente</strong> de revisión por el administrador o recepcionista.
            </div>

            <div class="mt-4 rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('cliente.boletos.confirmar-pago', $reserva) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <x-input-label for="comprobante" value="Adjuntar comprobante de pago" />
                        <input id="comprobante" name="comprobante" type="file" accept="image/*,.pdf"
                               class="mt-1 block w-full text-sm text-[#12241C]/70 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#1B6B45]/10 file:text-[#1B6B45]" />
                        <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                    </div>

                    @if($reserva->boletos->first()?->comprobante_url)
                        <div class="mb-4 text-sm text-[#12241C]/60">
                            Comprobante ya enviado: <a href="{{ $reserva->boletos->first()->comprobante_url }}" target="_blank" class="text-[#1B6B45] underline">Ver comprobante</a>
                        </div>
                    @endif

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-[#6FCF64] px-4 py-3 text-sm font-bold text-[#0B2A1E] hover:bg-[#9BE28C]">
                        Enviar comprobante y notificar al admin
                    </button>
                </form>
            </div>

            <div class="mt-4 flex flex-col gap-3">
                <a href="https://wa.me/59171005452" target="_blank" class="inline-flex items-center justify-center rounded-full bg-[#25D366] px-4 py-3 text-sm font-semibold text-white hover:brightness-95">
                    Enviar confirmación al WhatsApp +591 71005452
                </a>
                <a href="{{ route('cliente.boletos.index') }}" class="inline-flex items-center justify-center rounded-full bg-[#0B2A1E] px-4 py-3 text-sm font-semibold text-white hover:bg-[#1B6B45]">
                    Ver mis boletos
                </a>
            </div>
        </div>
    </div>
</x-cliente-layout>
