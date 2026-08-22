<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Nuevo cliente</h2>
            <p class="page-subtitle">Completa los datos principales para registrar un cliente nuevo.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-3xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('parametrizacion.clientes.store') }}">
                @csrf
                @include('parametrizacion.clientes._form', ['btnTexto' => 'Crear Cliente'])
            </form>
        </div>
    </div>
</x-app-layout>
