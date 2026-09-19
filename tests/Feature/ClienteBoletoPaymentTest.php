<?php

namespace Tests\Feature;

use App\Models\AsientoViaje;
use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Ruta;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClienteBoletoPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_can_mark_pending_qr_boleto_as_paid_and_receive_internal_notification(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'cedula' => '1234567',
            'email' => 'juan@example.com',
            'contrasena' => bcrypt('password'),
            'telefono' => '70123456',
            'direccion' => 'Santa Cruz',
            'fecha_nacimiento' => '1990-01-01',
            'estado_base' => 1,
        ]);

        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Cochabamba',
            'distancia_km' => 700,
            'precio_base' => 120,
            'estado_base' => 1,
        ]);

        $bus = Bus::create([
            'placa' => '123ABC',
            'capacidad' => 20,
            'modelo' => 'Mercedes',
            'tipo_bus' => 'Ejecutivo',
            'estado_base' => 1,
        ]);

        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-08-01',
            'hora_salida' => '08:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        $boleto = Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => $cliente->nombre_completo,
            'ci_pasajero' => $cliente->cedula,
            'telefono_pasajero' => $cliente->telefono,
            'precio' => 120,
            'metodo_pago' => 'QR',
            'estado' => 'pendiente',
            'expira_en' => now()->addMinutes(15),
            'estado_base' => 1,
        ]);

        $response = $this->actingAs($cliente, 'cliente')->post(route('cliente.boletos.confirmar-pago', $reserva));

        $response->assertRedirect(route('cliente.boletos.index'));
        $this->assertDatabaseHas('boletos', ['id' => $boleto->id, 'estado' => 'confirmado']);
        $this->assertDatabaseHas('notificaciones', ['cliente_id' => $cliente->id, 'titulo' => 'Pago recibido']);
        $this->assertSame('confirmada', $reserva->fresh()->estado);
    }

    public function test_cliente_can_upload_comprobante_and_keep_payment_pending_for_admin(): void
    {
        Storage::fake('public');

        $cliente = Cliente::create([
            'nombre' => 'Carla',
            'apellido' => 'Ruiz',
            'cedula' => '9999999',
            'email' => 'carla@example.com',
            'contrasena' => bcrypt('password'),
            'telefono' => '71234567',
            'direccion' => 'Santa Cruz',
            'fecha_nacimiento' => '1995-05-05',
            'estado_base' => 1,
        ]);

        $ruta = Ruta::create([
            'origen' => 'Cochabamba',
            'destino' => 'La Paz',
            'distancia_km' => 500,
            'precio_base' => 100,
            'estado_base' => 1,
        ]);

        $bus = Bus::create([
            'placa' => '789DEF',
            'capacidad' => 20,
            'modelo' => 'Scania',
            'tipo_bus' => 'VIP',
            'estado_base' => 1,
        ]);

        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-08-03',
            'hora_salida' => '14:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        $boleto = Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '2A',
            'nombre_pasajero' => $cliente->nombre_completo,
            'ci_pasajero' => $cliente->cedula,
            'telefono_pasajero' => $cliente->telefono,
            'precio' => 100,
            'metodo_pago' => 'QR',
            'estado' => 'pendiente',
            'expira_en' => now()->addMinutes(15),
            'estado_base' => 1,
        ]);

        $file = UploadedFile::fake()->image('comprobante.jpg');

        $response = $this->actingAs($cliente, 'cliente')->post(route('cliente.boletos.confirmar-pago', $reserva), [
            'comprobante' => $file,
        ]);

        $response->assertRedirect(route('cliente.boletos.qr', $reserva));
        Storage::disk('public')->assertExists('comprobantes/' . $file->hashName());

        $this->assertDatabaseHas('boletos', [
            'id' => $boleto->id,
            'estado' => 'pendiente',
            'comprobante_path' => 'comprobantes/' . $file->hashName(),
        ]);
    }

    public function test_cliente_can_send_tipo_pasajero_and_persist_booking_type_flags(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Ana',
            'apellido' => 'Mendoza',
            'cedula' => '7654321',
            'email' => 'ana@example.com',
            'contrasena' => bcrypt('password'),
            'telefono' => '70987654',
            'direccion' => 'Santa Cruz',
            'fecha_nacimiento' => '1992-02-02',
            'estado_base' => 1,
        ]);

        $ruta = Ruta::create([
            'origen' => 'La Paz',
            'destino' => 'Santa Cruz',
            'distancia_km' => 800,
            'precio_base' => 150,
            'estado_base' => 1,
        ]);

        $bus = Bus::create([
            'placa' => '456XYZ',
            'capacidad' => 20,
            'modelo' => 'Volvo',
            'tipo_bus' => 'Premium',
            'estado_base' => 1,
        ]);

        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => now()->addDay()->toDateString(),
            'hora_salida' => '10:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
            'estado_base' => 1,
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        $response = $this->actingAs($cliente, 'cliente')->post(route('cliente.boletos.store'), [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => ['1A'],
            'tipo_pasajero' => ['normal'],
            'metodo_pago' => 'QR',
        ]);

        $response->assertRedirect(route('cliente.boletos.qr', $reserva));
        $this->assertDatabaseHas('boletos', [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'espacio_extra' => 0,
            'mascota' => 0,
        ]);
    }

    public function test_cliente_forces_labels_for_comodidad_and_mascota_even_if_passenger_name_array_is_sent(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Ana',
            'apellido' => 'Mendoza',
            'cedula' => '7654321',
            'email' => 'ana2@example.com',
            'contrasena' => bcrypt('password'),
            'telefono' => '70987654',
            'direccion' => 'Santa Cruz',
            'fecha_nacimiento' => '1992-02-02',
            'estado_base' => 1,
        ]);

        $ruta = Ruta::create([
            'origen' => 'La Paz',
            'destino' => 'Santa Cruz',
            'distancia_km' => 800,
            'precio_base' => 150,
            'estado_base' => 1,
        ]);

        $bus = Bus::create([
            'placa' => '789XYZ',
            'capacidad' => 20,
            'modelo' => 'Volvo',
            'tipo_bus' => 'Premium',
            'estado_base' => 1,
        ]);

        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => now()->addDay()->toDateString(),
            'hora_salida' => '10:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
            'estado_base' => 1,
        ]);

        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1B',
            'estado' => 'disponible',
            'estado_base' => 1,
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 2,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        $response = $this->actingAs($cliente, 'cliente')->post(route('cliente.boletos.store'), [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => ['1A', '1B'],
            'tipo_pasajero' => ['comodidad', 'mascota'],
            'nombre_pasajero' => ['melisa tapia', 'melisa tapia'],
            'ci_pasajero' => ['31231232', '31231232'],
            'telefono_pasajero' => ['71123123', '71123123'],
            'metodo_pago' => 'QR',
        ]);

        $response->assertRedirect(route('cliente.boletos.qr', $reserva));
        $this->assertDatabaseHas('boletos', [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => 'Comodidad (espacio extra)',
            'espacio_extra' => 1,
            'mascota' => 0,
        ]);
        $this->assertDatabaseHas('boletos', [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1B',
            'nombre_pasajero' => 'Mascota',
            'espacio_extra' => 0,
            'mascota' => 1,
        ]);
    }

    public function test_cliente_receives_validation_error_when_trying_to_reserve_an_already_taken_seat(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Ana',
            'apellido' => 'Mendoza',
            'cedula' => '7654321',
            'email' => 'ana@example.com',
            'contrasena' => bcrypt('password'),
            'telefono' => '70987654',
            'direccion' => 'Santa Cruz',
            'fecha_nacimiento' => '1992-02-02',
            'estado_base' => 1,
        ]);

        $ruta = Ruta::create([
            'origen' => 'La Paz',
            'destino' => 'Santa Cruz',
            'distancia_km' => 800,
            'precio_base' => 150,
            'estado_base' => 1,
        ]);

        $bus = Bus::create([
            'placa' => '456XYZ',
            'capacidad' => 20,
            'modelo' => 'Volvo',
            'tipo_bus' => 'Premium',
            'estado_base' => 1,
        ]);

        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-08-02',
            'hora_salida' => '10:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
            'estado_base' => 1,
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => $cliente->nombre_completo,
            'ci_pasajero' => $cliente->cedula,
            'telefono_pasajero' => $cliente->telefono,
            'precio' => 150,
            'metodo_pago' => 'QR',
            'estado' => 'confirmado',
            'estado_base' => 1,
        ]);

        $response = $this->actingAs($cliente, 'cliente')->post(route('cliente.boletos.store'), [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => ['1A'],
            'tipo_pasajero' => ['normal'],
            'metodo_pago' => 'QR',
        ]);

        $response->assertSessionHasErrors('numero_asiento');
        $response->assertRedirect();
    }
}
