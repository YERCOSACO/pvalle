@props([
    'titulo',
    'valor' => null,
    'subtitulo',
    'variacion' => null,
    'formato' => 'entero',
    'ayuda' => null,
    'mostrarVariacion' => false,
])

@php
    $valorFormateado = $valor === null
        ? '—'
        : match ($formato) {
            'moneda' => 'Bs '.number_format((float) $valor, 2, ',', '.'),
            'porcentaje' => number_format((float) $valor, 1, ',', '.').'%',
            default => number_format((float) $valor, 0, ',', '.'),
        };
    $variacionClase = $variacion > 0
        ? 'text-emerald-700'
        : ($variacion < 0 ? 'text-rose-700' : 'text-slate-500');
@endphp

<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @if ($ayuda) title="{{ $ayuda }}" @endif>
    <p class="text-sm font-semibold text-slate-500">{{ $titulo }}</p>
    <p class="mt-3 text-2xl font-extrabold text-slate-950">{{ $valorFormateado }}</p>
    <p class="mt-2 text-xs leading-5 text-slate-500">{{ $subtitulo }}</p>
    @if ($mostrarVariacion)
        @if ($variacion === null)
            <p class="mt-3 text-xs font-semibold text-slate-500">Sin datos previos</p>
        @else
            <p class="mt-3 text-xs font-semibold {{ $variacionClase }}">
                @if ($variacion > 0)
                    ▲
                @elseif ($variacion < 0)
                    ▼
                @endif
                {{ number_format(abs($variacion), 1, ',', '.') }}% vs mes anterior
            </p>
        @endif
    @endif
</article>
