<?php

namespace App\Services;

use App\Models\AsientoViaje;
use App\Models\Boleto;
use App\Models\Viaje;
use Illuminate\Validation\ValidationException;

class ViajeAsientosService
{
    public function generarNumeros(int $capacidad): array
    {
        $asientos = [];
        $letras = ['A', 'B', 'C', 'D'];
        $filas = (int) ceil($capacidad / count($letras));

        for ($fila = 1; $fila <= $filas; $fila++) {
            foreach ($letras as $letra) {
                if (count($asientos) >= $capacidad) {
                    return $asientos;
                }

                $asientos[] = $fila . $letra;
            }
        }

        return $asientos;
    }

    public function crearParaViaje(Viaje $viaje): void
    {
        $viaje->loadMissing('bus');

        foreach ($this->generarNumeros($viaje->bus->capacidad) as $numeroAsiento) {
            AsientoViaje::firstOrCreate(
                [
                    'viaje_id' => $viaje->id,
                    'numero_asiento' => $numeroAsiento,
                ],
                [
                    'estado' => 'disponible',
                    'estado_base' => 1,
                ]
            );
        }
    }

    public function disponibles(int $viajeId): array
    {
        $this->liberarBoletosExpirados($viajeId);
        $this->sincronizarConBoletos($viajeId);

        return AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->where('estado', 'disponible')
            ->orderBy('numero_asiento')
            ->pluck('numero_asiento')
            ->all();
    }

    public function todos(int $viajeId): array
    {
        $this->liberarBoletosExpirados($viajeId);
        $this->sincronizarConBoletos($viajeId);

        return AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->orderBy('numero_asiento')
            ->pluck('numero_asiento')
            ->all();
    }

    public function ocupados(int $viajeId): array
    {
        $this->liberarBoletosExpirados($viajeId);
        $this->sincronizarConBoletos($viajeId);

        return AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->whereIn('estado', ['ocupado', 'bloqueado'])
            ->pluck('numero_asiento')
            ->all();
    }

    public function reservar(int $viajeId, array $numerosAsiento): void
    {
        $numerosAsiento = array_values(array_unique($numerosAsiento));
        $this->liberarBoletosExpirados($viajeId);

        $asientos = AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', $numerosAsiento)
            ->where('estado_base', 1)
            //5/////////////////////////////////////para la duplicidad/////
            ->lockForUpdate()
            ->get()
            ->keyBy('numero_asiento');

        foreach ($numerosAsiento as $numeroAsiento) {
            $asiento = $asientos->get($numeroAsiento);

            if (! $asiento || $asiento->estado !== 'disponible') {
                throw ValidationException::withMessages([
                    'numero_asiento' => "El asiento {$numeroAsiento} no está disponible.",
                ]);
            }
        }

        AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', $numerosAsiento)
            ->update(['estado' => 'ocupado']);
    }

    public function liberar(int $viajeId, array $numerosAsiento): void
    {
        AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', array_values(array_unique($numerosAsiento)))
            ->where('estado', 'ocupado')
            ->update(['estado' => 'disponible']);
    }

    private function liberarBoletosExpirados(int $viajeId): void
    {
        $numerosExpirados = Boleto::query()
            ->where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->where('estado', 'pendiente')
            ->whereNotNull('expira_en')
            ->where('expira_en', '<=', now())
            ->pluck('numero_asiento');

        if ($numerosExpirados->isEmpty()) {
            return;
        }

        AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', $numerosExpirados)
            ->where('estado', 'ocupado')
            ->update(['estado' => 'disponible']);

        Boleto::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', $numerosExpirados)
            ->where('estado', 'pendiente')
            ->update(['estado' => 'cancelado', 'estado_base' => 0]);
    }

    private function sincronizarConBoletos(int $viajeId): void
    {
        $numerosOcupados = Boleto::query()
            ->where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->where(function ($query) {
                $query->where('estado', 'confirmado')
                    ->orWhere(function ($query) {
                        $query->where('estado', 'pendiente')
                            ->where(function ($query) {
                                $query->whereNull('expira_en')
                                    ->orWhere('expira_en', '>', now());
                            });
                    });
            })
            ->pluck('numero_asiento');

        if ($numerosOcupados->isEmpty()) {
            return;
        }

        AsientoViaje::query()
            ->where('viaje_id', $viajeId)
            ->whereIn('numero_asiento', $numerosOcupados)
            ->where('estado_base', 1)
            ->where('estado', 'disponible')
            ->update(['estado' => 'ocupado']);
    }
}
