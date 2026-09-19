<x-cliente-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-[2rem] bg-[#0B2A1E] p-6 text-white shadow-xl shadow-black/20 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#9BE28C]">Nueva reserva</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight">Prepara tu viaje</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Indica cuántos boletos necesitas. En el siguiente paso podrás elegir un viaje futuro y sus asientos disponibles.</p>
        </div>

        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('cliente.reservas.store') }}">

                //4///////////////////////////////////////////////////////
                @csrf
                <input type="hidden" name="viaje_id" value="{{ old('viaje_id', $viajeId) }}">
                <div>
                    <x-input-label for="cantidad" value="¿Cuántos boletos necesitas?" />
                    <p class="mt-1 text-sm text-[#12241C]/50">La cantidad se confirmará según los asientos disponibles del viaje.</p>
                    <input id="cantidad" name="cantidad" type="number" min="1" max="5" value="{{ old('cantidad', $cantidad) }}"
                           class="mt-3 block w-full rounded-lg border-black/15 bg-[#F6F3E9] text-sm shadow-sm focus:border-[#238453] focus:ring-[#238453]">
                    <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-black/10 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('cliente.reservas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-black/15 px-4 py-3 text-sm font-semibold text-[#12241C]/70 hover:bg-[#F6F3E9]">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#6FCF64] px-4 py-3 text-sm font-bold text-[#0B2A1E] hover:bg-[#9BE28C]">Continuar a los asientos</button>
                </div>
            </form>
        </div>
    </div>
</x-cliente-layout>
