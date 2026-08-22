<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Editar usuario</h2>
            <p class="page-subtitle">Actualiza la información de {{ $usuario->name }}.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-2xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('seguridad.usuarios.update', $usuario) }}">
                @csrf @method('PUT')
                @include('seguridad.usuarios._form', ['btnTexto' => 'Actualizar'])
            </form>
        </div>
    </div>
</x-app-layout>
