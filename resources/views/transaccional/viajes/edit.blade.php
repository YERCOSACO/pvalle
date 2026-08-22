<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Editar viaje</h2>
            <p class="page-subtitle">Actualiza la salida correspondiente a {{ $viaje->ruta->nombre_ruta }}.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-3xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('transaccional.viajes.update', $viaje) }}">
                @csrf @method('PUT')
                @include('transaccional.viajes._form', ['btnTexto' => 'Actualizar Viaje'])
            </form>
        </div>
    </div>
</x-app-layout>
