@props([
    'href' => url('/'),
    'dark' => false,
    'compact' => false,
    'size' => null,
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <img
        src="{{ asset('images/logo.png') }}"
        alt="Mi Valle"
        class="{{ $size === 'header'
            ? 'h-16 w-24 object-contain sm:h-[4.5rem] sm:w-28'
            : ($compact
                ? 'h-16 w-[120px] object-contain sm:h-[4.5rem] sm:w-[140px]'
                : 'h-28 w-28 object-contain sm:h-36 sm:w-36') }}"
    >
</a>