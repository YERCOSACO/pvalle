<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Nuevo viaje</h2>
            <p class="page-subtitle">Configura la ruta, el bus y la fecha de salida.</p>
        </div>
    </x-slot>

    <div class="page-shell mx-auto max-w-3xl">
        <div class="page-panel form-card p-6 sm:p-8">
            <form method="POST" action="{{ route('transaccional.viajes.store') }}">
                @csrf
                @include('transaccional.viajes._form', ['btnTexto' => 'Crear Viaje'])
            </form>
        </div>
    </div>
</x-app-layout>
