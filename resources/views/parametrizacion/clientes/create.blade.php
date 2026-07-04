<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Cliente
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('parametrizacion.clientes.store') }}">
                @csrf
                @include('parametrizacion.clientes._form', ['btnTexto' => 'Crear Cliente'])
            </form>
        </div>
    </div>
</x-app-layout>