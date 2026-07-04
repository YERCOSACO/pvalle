<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Asiento {{ $asientoviaje->numero_asiento }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('transaccional.asientoviaje.update', $asientoviaje) }}">
                @csrf @method('PUT')
                @include('transaccional.asientoviaje._form')
            </form>
        </div>
    </div>
</x-app-layout>