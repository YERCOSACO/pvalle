<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Rol — {{ $rol->name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('seguridad.roles.update', $rol) }}">
                @csrf @method('PUT')
                @include('seguridad.roles._form', ['btnTexto' => 'Actualizar Rol'])
            </form>
        </div>
    </div>
</x-app-layout>