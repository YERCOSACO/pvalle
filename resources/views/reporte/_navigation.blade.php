@php
    $reportes = [
        'boletos' => ['label' => 'Boletos', 'route' => 'reporte.boletos'],
        'viajes' => ['label' => 'Viajes', 'route' => 'reporte.viajes'],
        'encomiendas' => ['label' => 'Encomiendas', 'route' => 'reporte.encomiendas'],
    ];
@endphp

<nav aria-label="Tipos de reportes" class="mb-6 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
    @can('reportes.ver')
        @foreach($reportes as $key => $reporte)
            <a href="{{ route($reporte['route']) }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                   'bg-teal-700 text-white shadow-sm' => $active === $key,
                   'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $active !== $key,
               ])
               @if($active === $key) aria-current="page" @endif>
                Reporte de {{ $reporte['label'] }}
            </a>
        @endforeach
    @endcan
    @can('asientoviaje.ver')
        <a href="{{ route('parametrizacion.asientoviaje.index') }}"
           @class([
               'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
               'bg-teal-700 text-white shadow-sm' => $active === 'asientos',
               'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $active !== 'asientos',
           ])
           @if($active === 'asientos') aria-current="page" @endif>
            Reporte de asientos
        </a>
    @endcan
</nav>
