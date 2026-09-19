<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asientos</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
            Los asientos se generan automáticamente al crear un Viaje.
            <a href="{{ route('transaccional.viajes.create') }}" class="text-indigo-600 hover:underline">
                Crear un nuevo viaje
            </a>
        </div>
    </div>
</x-app-layout>