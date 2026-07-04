<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Boleto</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('transaccional.boletos.store') }}">
                @csrf
                @include('transaccional.boletos._form', ['btnTexto' => 'Registrar Boleto'])
            </form>
        </div>
    </div>
</x-app-layout>