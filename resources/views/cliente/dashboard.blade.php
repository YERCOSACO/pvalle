<x-cliente-layout>
    <div class="space-y-8">
        <section class="overflow-hidden rounded-[2rem] bg-[#52dd5e] px-6 py-8 text-white shadow-xl shadow-black/20 sm:px-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.6em] text-[#f3f7f3]">Panel de viaje</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Bienvenid@ : {{ $cliente->nombre_completo }}</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-[#f3f7f3]">Consulta tus reservas, revisa tus boletos y mantente al día con cada aviso.</p>
                </div>
                <a href="{{ route('cliente.reservas.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#6FCF64] px-4 py-3 text-sm font-bold text-[#0B2A1E] hover:bg-[#990e19]">Reservar un viaje</a>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('cliente.reservas.index') }}" class="group rounded-2xl border border-black/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:border-[#1B6B45]/40 hover:shadow-md">
                <p class="text-sm font-semibold text-[#12241C]/60">Reservas</p>
                <p class="mt-3 text-3xl font-bold text-[#12241C]">{{ $reservasCount }}</p>
                <p class="mt-3 text-sm font-semibold text-[#1B6B45]">Ver reservas <span aria-hidden="true">→</span></p>
            </a>
            <a href="{{ route('cliente.boletos.index') }}" class="group rounded-2xl border border-black/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:border-[#1B6B45]/40 hover:shadow-md">
                <p class="text-sm font-semibold text-[#12241C]/60">Boletos</p>
                <p class="mt-3 text-3xl font-bold text-[#12241C]">{{ $boletosCount }}</p>
                <p class="mt-3 text-sm font-semibold text-[#1B6B45]">Ver boletos <span aria-hidden="true">→</span></p>
            </a>
            <a href="{{ route('cliente.encomiendas.index') }}" class="group rounded-2xl border border-black/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:border-[#1B6B45]/40 hover:shadow-md">
                <p class="text-sm font-semibold text-[#12241C]/60">Encomiendas</p>
                <p class="mt-3 text-3xl font-bold text-[#12241C]">{{ $encomiendasCount }}</p>
                <p class="mt-3 text-sm font-semibold text-[#1B6B45]">Ver encomiendas <span aria-hidden="true">→</span></p>
            </a>
            <a href="{{ route('cliente.notificaciones.index') }}" class="group rounded-2xl border border-black/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:border-[#1B6B45]/40 hover:shadow-md">
                <p class="text-sm font-semibold text-[#12241C]/60">Avisos recientes</p>
                <p class="mt-3 text-3xl font-bold text-[#12241C]">{{ $notificaciones->count() }}</p>
                <p class="mt-3 text-sm font-semibold text-[#1B6B45]">Ver avisos <span aria-hidden="true">→</span></p>
            </a>
        </div>

        <section class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm sm:p-7">
            <div class="flex items-center justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#12241C]/40">Actividad</p><h2 class="mt-1 text-xl font-bold text-[#12241C]">Últimos avisos</h2></div>
                <a href="{{ route('cliente.notificaciones.index') }}" class="text-sm font-semibold text-[#1B6B45] hover:text-[#0B2A1E]">Ver todos</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($notificaciones as $notificacion)
                    <div class="rounded-xl border border-black/5 bg-[#F6F3E9] p-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-[#12241C]">{{ $notificacion->titulo }}</p>
                            <span class="shrink-0 text-xs text-[#12241C]/50">{{ optional($notificacion->fecha_envio)->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[#12241C]/70">{{ $notificacion->mensaje }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#12241C]/50">No hay notificaciones recientes.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-cliente-layout>
