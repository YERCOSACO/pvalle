<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Asignación — {{ $viaje->ruta->nombre_ruta }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('transaccional.asignacionconductor.update', $viaje) }}">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <x-input-label value="Viaje" />
                        <p class="mt-1 font-medium text-gray-800">
                            {{ $viaje->ruta->nombre_ruta }} — {{ $viaje->fecha_viaje->format('d/m/Y') }} {{ $viaje->hora_salida }}
                        </p>
                    </div>

                    <div>
                        <x-input-label for="conductor_principal" value="Conductor Principal *" />
                        <select name="conductor_principal" id="conductor_principal"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($conductores as $conductor)
                                <option value="{{ $conductor->id }}"
                                    {{ old('conductor_principal', $principal->conductor_id ?? '') == $conductor->id ? 'selected' : '' }}>
                                    {{ $conductor->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('conductor_principal')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="conductor_relevo" value="Conductor Relevo *" />
                        <select name="conductor_relevo" id="conductor_relevo"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($conductores as $conductor)
                                <option value="{{ $conductor->id }}"
                                    {{ old('conductor_relevo', $relevo->conductor_id ?? '') == $conductor->id ? 'selected' : '' }}>
                                    {{ $conductor->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('conductor_relevo')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="ayudante" value="Ayudante (opcional)" />
                        <select name="ayudante" id="ayudante"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Ninguno --</option>
                            @foreach($conductores as $conductor)
                                <option value="{{ $conductor->id }}"
                                    {{ old('ayudante', $ayudante->conductor_id ?? '') == $conductor->id ? 'selected' : '' }}>
                                    {{ $conductor->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('ayudante')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>Actualizar</x-primary-button>
                        <a href="{{ route('transaccional.asignacionconductor.index') }}"
                           class="form-cancel">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>