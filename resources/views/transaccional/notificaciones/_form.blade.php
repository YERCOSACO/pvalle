<div class="space-y-4">
    <div>
        <x-input-label for="cliente_id" value="Cliente" />
        <select name="cliente_id" id="cliente_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}"
                    {{ old('cliente_id', $notificacion->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->nombre_completo }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('cliente_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="titulo" value="Título" />
        <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full"
            value="{{ old('titulo', $notificacion->titulo ?? '') }}" />
        <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="mensaje" value="Mensaje" />
        <textarea id="mensaje" name="mensaje" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('mensaje', $notificacion->mensaje ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('mensaje')" class="mt-1" />
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <x-input-label for="tipo" value="Tipo" />
            <select name="tipo" id="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="info" {{ old('tipo', $notificacion->tipo ?? '') == 'info' ? 'selected' : '' }}>Info</option>
                <option value="alerta" {{ old('tipo', $notificacion->tipo ?? '') == 'alerta' ? 'selected' : '' }}>Alerta</option>
                <option value="urgente" {{ old('tipo', $notificacion->tipo ?? '') == 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>

        @isset($clientes)
        @if(!isset($notificacion))
        <div>
            <x-input-label for="canal" value="Canal" />
            <select name="canal" id="canal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="sistema">Sistema</option>
                <option value="email">Email</option>
                <option value="sms">SMS</option>
            </select>
        </div>
        @endif
        @endisset

        <div>
            <x-input-label for="prioridad" value="Prioridad" />
            <select name="prioridad" id="prioridad" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="baja" {{ old('prioridad', $notificacion->prioridad ?? '') == 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="normal" {{ old('prioridad', $notificacion->prioridad ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="alta" {{ old('prioridad', $notificacion->prioridad ?? '') == 'alta' ? 'selected' : '' }}>Alta</option>
            </select>
        </div>
    </div>

    @isset($notificacion)
    <div>
        <x-input-label for="estado" value="Estado" />
        <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="pendiente" {{ old('estado', $notificacion->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="enviada" {{ old('estado', $notificacion->estado) == 'enviada' ? 'selected' : '' }}>Enviada</option>
            <option value="leida" {{ old('estado', $notificacion->estado) == 'leida' ? 'selected' : '' }}>Leída</option>
        </select>
    </div>
    @endisset

    @if(!isset($notificacion))
    <div x-data="{ origenTipo: '' }">
        <x-input-label value="Vincular a (opcional)" />
        <select name="origen_tipo" x-model="origenTipo"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Notificación general --</option>
            <option value="incidencia">Incidencia de viaje</option>
            <option value="encomienda">Encomienda</option>
            <option value="boleto">Boleto</option>
        </select>

        <div x-show="origenTipo === 'incidencia'" class="mt-2">
            <select name="origen_id" class="block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Seleccionar incidencia --</option>
                @foreach($incidencias as $incidencia)
                    <option value="{{ $incidencia->id }}">
                        {{ $incidencia->tipoIncidencia->nombre ?? '' }} — {{ $incidencia->viaje->ruta->nombre_ruta ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div x-show="origenTipo === 'encomienda'" class="mt-2">
            <select name="origen_id" class="block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Seleccionar encomienda --</option>
                @foreach($encomiendas as $encomienda)
                    <option value="{{ $encomienda->id }}">
                        {{ $encomienda->destinatario_nombre }} — {{ $encomienda->nombre_remitente }}
                    </option>
                @endforeach
            </select>
        </div>

        <div x-show="origenTipo === 'boleto'" class="mt-2">
            <select name="origen_id" class="block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Seleccionar boleto --</option>
                @foreach($boletos as $boleto)
                    <option value="{{ $boleto->id }}">
                        {{ $boleto->reserva->cliente->nombre_completo ?? 'Sin cliente' }} — {{ $boleto->viaje->ruta->nombre_ruta ?? '' }} — Asiento {{ $boleto->numero_asiento }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $btnTexto ?? 'Guardar' }}</x-primary-button>
        <a href="{{ route('transaccional.notificaciones.index') }}"
           class="form-cancel">Cancelar</a>
    </div>
</div>