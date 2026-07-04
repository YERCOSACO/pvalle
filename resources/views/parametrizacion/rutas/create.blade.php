<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Ruta</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('parametrizacion.rutas.store') }}">
                @csrf
                @include('parametrizacion.rutas._form', ['btnTexto' => 'Crear Ruta'])
            </form>
        </div>
    </div>
</x-app-layout>