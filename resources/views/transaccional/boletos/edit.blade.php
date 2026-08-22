<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Boleto</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">

            <div class="bg-gray-50 rounded-lg p-4 mb-6 grid grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Viaje</span>
                    <p class="font-medium">{{ $boleto->viaje->ruta->nombre_ruta }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Fecha</span>
                    <p class="font-medium">{{ $boleto->viaje->fecha_viaje->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Asiento</span>
                    <p class="font-bold text-indigo-600 text-lg">{{ $boleto->numero_asiento }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Método de pago</span>
                    <p class="font-medium capitalize">{{ $boleto->metodo_pago }}</p>
                </div>
            </div>

            @if($boleto->estado === 'pendiente' && $boleto->expira_en)
                <div class="bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-3 text-sm mb-4">
                    ⏱️ Este boleto expira en <strong>{{ $boleto->minutos_restantes }} minutos</strong>
                    ({{ $boleto->expira_en->format('H:i') }})

                    <form method="POST" action="{{ route('transaccional.boletos.confirmar-pago', $boleto) }}" class="inline ml-3">
                        @csrf @method('PATCH')
                        <button class="btn-warning btn-sm">
                            Confirmar pago QR ahora
                        </button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('transaccional.boletos.update', $boleto) }}">
                @csrf @method('PUT')
                @include('transaccional.boletos._form', ['btnTexto' => 'Actualizar'])
            </form>
        </div>
    </div>
</x-app-layout>