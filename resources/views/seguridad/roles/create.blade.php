<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agregar Rol
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('seguridad.roles.store') }}">
                @csrf
                @include('seguridad.roles._form', ['btnTexto' => 'Crear Rol'])
            </form>
        </div>
    </div>
</x-app-layout>