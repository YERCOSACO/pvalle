<x-cliente-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-300">Nueva reserva</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight">Prepara tu viaje</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Indica cuántos boletos necesitas. En el siguiente paso podrás elegir el viaje y los asientos disponibles en el mapa del bus.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('cliente.reservas.store') }}">
                @csrf
                <div>
                    <x-input-label for="cantidad" value="¿Cuántos boletos necesitas?" />
                    <p class="mt-1 text-sm text-slate-500">Puedes reservar hasta 10 asientos por operación.</p>
                    <x-text-input id="cantidad" name="cantidad" type="number" min="1" max="10" value="{{ old('cantidad', 1) }}" class="mt-3" />
                    <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('cliente.reservas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Continuar a los asientos</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-cliente-layout>
