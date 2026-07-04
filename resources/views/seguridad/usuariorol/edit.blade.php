<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modificar Usuario-Rol — {{ $usuario->name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('seguridad.usuariorol.update', $usuario) }}">
                @csrf @method('PUT')
                @include('seguridad.usuariorol._form', ['btnTexto' => 'Actualizar'])
            </form>
        </div>
    </div>
</x-app-layout>