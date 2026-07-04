<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar — {{ $tipoEncomienda->nombre }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('parametrizacion.tipoencomiendas.update', $tipoEncomienda) }}">
                @csrf @method('PUT')
                @include('parametrizacion.tipoencomiendas._form', ['btnTexto' => 'Actualizar Tipo'])
            </form>
        </div>
    </div>
</x-app-layout>