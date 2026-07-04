<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Incidencia</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('transaccional.incidenciaviaje.update', $incidenciaviaje) }}">
                @csrf @method('PUT')
                @include('transaccional.incidenciaviaje._form', ['btnTexto' => 'Actualizar'])
            </form>
        </div>
    </div>
</x-app-layout>