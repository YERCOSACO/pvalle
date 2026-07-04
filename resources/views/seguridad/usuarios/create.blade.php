<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('seguridad.usuarios.store') }}">
                @csrf
                @include('seguridad.usuarios._form', ['btnTexto' => 'Crear Usuario'])
            </form>
        </div>
    </div>
</x-app-layout>