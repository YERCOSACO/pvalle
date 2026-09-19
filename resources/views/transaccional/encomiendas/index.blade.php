<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Encomiendas</h2>
            <a href="{{ route('transaccional.encomiendas.create') }}"
               class="btn-primary w-full sm:w-auto">
                Agregar Encomienda
            </a>
        </div>
    </x-slot>

    <div class="page-shell">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('transaccional.encomiendas.index') }}" class="search-form">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por destinatario o remitente..."
                   class="flex-1">
            <button type="submit" class="btn-primary w-full md:w-auto">
                Buscar Encomienda
            </button>
            @if(request('search'))
                <a href="{{ route('transaccional.encomiendas.index') }}"
                   class="btn-secondary w-full md:w-auto">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="table-card overflow-hidden">
            <div class="hidden overflow-x-auto md:block">
            <table class="table-shell min-w-[900px] table-fixed text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="w-10 px-3 py-3 text-left">#</th>
                        <th class="w-[125px] px-3 py-3 text-left">Remitente</th>
                        <th class="w-[125px] px-3 py-3 text-left">Destinatario</th>
                        <th class="w-[125px] px-3 py-3 text-left">Ruta</th>
                        <th class="w-[95px] px-3 py-3 text-left">Tipo</th>
                        <th class="w-14 px-3 py-3 text-left">Cant.</th>
                        <th class="w-24 px-3 py-3 text-left">Total</th>
                        <th class="w-24 px-3 py-3 text-left">Estado</th>
                        <th class="w-[135px] px-3 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($encomiendas as $encomienda)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="truncate px-3 py-3 font-medium" title="{{ $encomienda->nombre_remitente }}">{{ $encomienda->nombre_remitente }}</td>
                        <td class="truncate px-3 py-3 text-gray-600" title="{{ $encomienda->destinatario_nombre }}">{{ $encomienda->destinatario_nombre }}</td>
                        <td class="px-3 py-3 text-gray-500">{{ $encomienda->viaje->ruta->nombre_ruta }}</td>
                        <td class="px-3 py-3 text-gray-500">{{ $encomienda->tipoEncomienda->nombre }}</td>
                        <td class="px-3 py-3 text-gray-500">{{ $encomienda->cantidad }}</td>
                        <td class="px-3 py-3 text-gray-500">Bs {{ number_format($encomienda->total_pagar, 2) }}</td>
                        <td class="px-3 py-3">
                            @php
                                $colores = [
                                    'recibido'    => 'bg-yellow-100 text-yellow-700',
                                    'en transito' => 'bg-blue-100 text-blue-700',
                                    'entregado'   => 'bg-green-100 text-green-700',
                                    'cancelado'   => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="{{ $colores[$encomienda->estado] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-1 rounded-full capitalize">
                                {{ $encomienda->estado }}
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="table-actions min-w-[125px] flex-col items-stretch">
                                <a href="{{ route('transaccional.encomiendas.imprimir', $encomienda) }}"
                                   target="_blank"
                                   class="btn-success btn-sm w-full whitespace-nowrap px-2 text-[11px]">
                                    Imprimir ticket
                                </a>
                            <a href="{{ route('transaccional.encomiendas.edit', $encomienda) }}"
                                         class="btn-warning btn-sm w-full whitespace-nowrap px-2 text-[11px]">
                                Modificar
                            </a>
                            <form action="{{ route('transaccional.encomiendas.destroy', $encomienda) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar esta encomienda?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm w-full whitespace-nowrap px-2 text-[11px]">
                                    Eliminar
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">Sin encomiendas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div class="divide-y divide-slate-100 md:hidden">
                @forelse($encomiendas as $encomienda)
                    @php
                        $colores = [
                            'recibido'    => 'bg-yellow-100 text-yellow-700',
                            'en transito' => 'bg-blue-100 text-blue-700',
                            'entregado'   => 'bg-green-100 text-green-700',
                            'cancelado'   => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <article class="space-y-4 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Encomienda #{{ $loop->iteration }}</p>
                                <h3 class="mt-1 text-base font-bold text-slate-900">{{ $encomienda->nombre_remitente }}</h3>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $colores[$encomienda->estado] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $encomienda->estado }}
                            </span>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div><dt class="text-xs text-slate-400">Destinatario</dt><dd class="mt-1 font-medium text-slate-700">{{ $encomienda->destinatario_nombre }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Cantidad</dt><dd class="mt-1 font-medium text-slate-700">{{ $encomienda->cantidad }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Ruta</dt><dd class="mt-1 text-slate-600">{{ $encomienda->viaje->ruta->nombre_ruta }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Total</dt><dd class="mt-1 font-semibold text-slate-700">Bs {{ number_format($encomienda->total_pagar, 2) }}</dd></div>
                        </dl>
                        <div class="grid grid-cols-3 gap-2">
                            <a href="{{ route('transaccional.encomiendas.imprimir', $encomienda) }}" target="_blank" class="btn-primary btn-sm whitespace-nowrap">Imprimir</a>
                            <a href="{{ route('transaccional.encomiendas.edit', $encomienda) }}" class="btn-warning btn-sm whitespace-nowrap">Modificar</a>
                            <form action="{{ route('transaccional.encomiendas.destroy', $encomienda) }}" method="POST" onsubmit="return confirm('¿Eliminar esta encomienda?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm w-full whitespace-nowrap">Eliminar</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="empty-state">Sin encomiendas registradas.</p>
                @endforelse
            </div>
            @if($encomiendas->hasPages())
                <div class="px-6 py-4 border-t">{{ $encomiendas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>