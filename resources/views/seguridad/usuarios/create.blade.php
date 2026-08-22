<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Nuevo usuario</h2>
            <p class="page-subtitle">Crea una cuenta para el personal administrativo.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-2xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('seguridad.usuarios.store') }}">
                @csrf
                @include('seguridad.usuarios._form', ['btnTexto' => 'Crear Usuario'])
            </form>
        </div>
    </div>
</x-app-layout>
