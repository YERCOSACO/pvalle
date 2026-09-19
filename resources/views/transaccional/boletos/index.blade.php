<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Boletos por Viaje</h2>
            <a href="{{ route('transaccional.boletos.create') }}" class="btn-primary">
                Agregar Boleto
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto px-4">
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-xl text-sm shadow-sm">{{ session('success') }}</div>
        @endif

        {{-- Caso: No existen viajes cargados en el sistema --}}
        <div class="bg-white shadow-xl rounded-2xl p-8 text-center">
            <div class="text-6xl mb-4">🚌</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No hay viajes disponibles</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto mb-6">
                Para ver el mapa de asientos y administrar boletos, primero debes registrar un viaje activo.
            </p>
        </div>

    </div>
</x-app-layout>