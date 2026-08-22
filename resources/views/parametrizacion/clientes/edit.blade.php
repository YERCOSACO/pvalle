<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Editar cliente</h2>
            <p class="page-subtitle">Actualiza la información de {{ $cliente->nombre_completo }}.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-3xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('parametrizacion.clientes.update', $cliente) }}">
                @csrf @method('PUT')
                @include('parametrizacion.clientes._form', ['btnTexto' => 'Actualizar Cliente'])
            </form>
        </div>
    </div>
</x-app-layout>
