<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Encomienda</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('transaccional.encomiendas.store') }}">
                @csrf
                @include('transaccional.encomiendas._form', ['btnTexto' => 'Registrar Encomienda'])
            </form>
        </div>
    </div>
</x-app-layout>