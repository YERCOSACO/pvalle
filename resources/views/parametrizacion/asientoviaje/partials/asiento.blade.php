@php
    $estado = $asiento->estado;

    $clasesFondo = match($estado) {
        'ocupado'   => 'bg-rose-500',
        'bloqueado' => 'bg-gray-400',
        default     => 'bg-emerald-500',
    };

    $cursor = $estado === 'disponible' ? 'cursor-pointer hover:scale-110' : 'cursor-not-allowed opacity-90';
@endphp

<a href="{{ route('parametrizacion.asientoviaje.edit', $asiento) }}"
   class="block w-12 h-12 {{ $clasesFondo }} {{ $cursor }} rounded-lg shadow-md transition-transform duration-150 flex items-center justify-center">
    <span class="text-white font-bold text-sm">
        {{ $asiento->numero_asiento }}
    </span>
</a>