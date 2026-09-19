<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Boleto;
use App\Models\Encomienda;
use App\Models\Notificacion;
use App\Models\IncidenciaViaje;
use App\Models\AsientoViaje;
use App\Models\AsignacionConductor;
use App\Models\Viaje;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Conductor;
use App\Models\Bus;
use App\Models\Ruta;
use App\Models\TipoEncomienda;
use App\Models\TipoIncidencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllersTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario de prueba
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login y obtener token
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->token = $response['data']['token'];
    }

    /**
     * Helper para hacer requests autenticados
     */
    protected function authenticatedGet($uri, $headers = [])
    {
        return $this->getJson($uri, array_merge([
            'Authorization' => "Bearer {$this->token}",
        ], $headers));
    }

    protected function authenticatedPost($uri, $data = [], $headers = [])
    {
        return $this->postJson($uri, $data, array_merge([
            'Authorization' => "Bearer {$this->token}",
        ], $headers));
    }

    protected function authenticatedPut($uri, $data = [], $headers = [])
    {
        return $this->putJson($uri, $data, array_merge([
            'Authorization' => "Bearer {$this->token}",
        ], $headers));
    }

    protected function authenticatedDelete($uri, $headers = [])
    {
        return $this->deleteJson($uri, [], array_merge([
            'Authorization' => "Bearer {$this->token}",
        ], $headers));
    }

    // ────────────────────────────────────────────────────────
    // BOLETOS API TESTS
    // ────────────────────────────────────────────────────────

    public function test_boleto_api_index(): void
    {
        $this->createTestData();
        
        $response = $this->authenticatedGet('/api/boletos');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'reserva_id', 'viaje_id', 'numero_asiento', 'nombre_pasajero', 'precio', 'estado']
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }

    public function test_boleto_api_store(): void
    {
        $this->createTestData();
        $reserva = Reserva::first();
        $viaje = Viaje::first();

        $response = $this->authenticatedPost('/api/boletos', [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '2A',
            'nombre_pasajero' => 'Juan Pérez',
            'ci_pasajero' => '12345678',
            'telefono_pasajero' => '76543210',
            'precio' => 150.50,
            'metodo_pago' => 'QR',
            'estado' => 'pendiente',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.nombre_pasajero', 'Juan Pérez');
    }

    public function test_boleto_api_show(): void
    {
        $this->createTestData();
        $boleto = Boleto::first();

        $response = $this->authenticatedGet("/api/boletos/{$boleto->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $boleto->id);
    }

    public function test_boleto_api_update(): void
    {
        $this->createTestData();
        $boleto = Boleto::first();

        $response = $this->authenticatedPut("/api/boletos/{$boleto->id}", [
            'nombre_pasajero' => 'María García',
            'precio' => 200.00,
            'metodo_pago' => 'efectivo',
            'estado' => 'confirmado',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombre_pasajero', 'María García');
    }

    public function test_boleto_api_destroy(): void
    {
        $this->createTestData();
        $boleto = Boleto::first();

        $response = $this->authenticatedDelete("/api/boletos/{$boleto->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) Boleto::find($boleto->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // ENCOMIENDAS API TESTS
    // ────────────────────────────────────────────────────────

    public function test_encomienda_api_index(): void
    {
        $this->createTestData();

        $response = $this->authenticatedGet('/api/encomiendas');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'destinatario_nombre', 'viaje_id', 'tipo_encomienda_id', 'cantidad', 'total_pagar']
            ],
            'meta'
        ]);
    }

    public function test_encomienda_api_store(): void
    {
        $this->createTestData();
        $viaje = Viaje::first();
        $tipoEncomienda = TipoEncomienda::first();

        $response = $this->authenticatedPost('/api/encomiendas', [
            'destinatario_nombre' => 'María García',
            'destinatario_ci' => '87654321',
            'destinatario_telefono' => '67890123',
            'viaje_id' => $viaje->id,
            'tipo_encomienda_id' => $tipoEncomienda->id,
            'cantidad' => 2,
            'total_pagar' => 100.00,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.destinatario_nombre', 'María García');
    }

    public function test_encomienda_api_show(): void
    {
        $this->createTestData();
        $encomienda = Encomienda::first();

        $response = $this->authenticatedGet("/api/encomiendas/{$encomienda->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $encomienda->id);
    }

    public function test_encomienda_api_update(): void
    {
        $this->createTestData();
        $encomienda = Encomienda::first();

        $response = $this->authenticatedPut("/api/encomiendas/{$encomienda->id}", [
            'destinatario_nombre' => 'Pedro López',
            'destinatario_ci' => '11223344',
            'cantidad' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.destinatario_nombre', 'Pedro López');
    }

    public function test_encomienda_api_destroy(): void
    {
        $this->createTestData();
        $encomienda = Encomienda::first();

        $response = $this->authenticatedDelete("/api/encomiendas/{$encomienda->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) Encomienda::find($encomienda->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // NOTIFICACIONES API TESTS
    // ────────────────────────────────────────────────────────

    public function test_notificacion_api_index(): void
    {
        $this->createTestData();

        $response = $this->authenticatedGet('/api/notificaciones');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'cliente_id', 'titulo', 'mensaje', 'tipo', 'estado']
            ],
            'meta'
        ]);
    }

    public function test_notificacion_api_store(): void
    {
        $this->createTestData();
        $cliente = Cliente::first();

        $response = $this->authenticatedPost('/api/notificaciones', [
            'cliente_id' => $cliente->id,
            'titulo' => 'Tu boleto ha sido confirmado',
            'mensaje' => 'Tu boleto para el viaje del 15/09/2026 ha sido confirmado.',
            'tipo' => 'info',
            'canal' => 'sistema',
            'prioridad' => 'normal',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.titulo', 'Tu boleto ha sido confirmado');
    }

    public function test_notificacion_api_show(): void
    {
        $this->createTestData();
        $notificacion = Notificacion::first();

        $response = $this->authenticatedGet("/api/notificaciones/{$notificacion->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $notificacion->id);
    }

    public function test_notificacion_api_update(): void
    {
        $this->createTestData();
        $notificacion = Notificacion::first();

        $response = $this->authenticatedPut("/api/notificaciones/{$notificacion->id}", [
            'titulo' => 'Actualización importante',
            'mensaje' => 'Se ha actualizado tu información.',
            'estado' => 'leida',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.titulo', 'Actualización importante');
    }

    public function test_notificacion_api_destroy(): void
    {
        $this->createTestData();
        $notificacion = Notificacion::first();

        $response = $this->authenticatedDelete("/api/notificaciones/{$notificacion->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) Notificacion::find($notificacion->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // INCIDENCIAS DE VIAJE API TESTS
    // ────────────────────────────────────────────────────────

    public function test_incidencia_viaje_api_index(): void
    {
        $this->createTestData();

        $response = $this->authenticatedGet('/api/incidenciasviaje');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'viaje_id', 'tipo_incidencia_id', 'fecha_inicio', 'descripcion_detalle']
            ],
            'meta'
        ]);
    }

    public function test_incidencia_viaje_api_store(): void
    {
        $this->createTestData();
        $viaje = Viaje::first();
        $tipoIncidencia = TipoIncidencia::first();

        $response = $this->authenticatedPost('/api/incidenciasviaje', [
            'viaje_id' => $viaje->id,
            'tipo_incidencia_id' => $tipoIncidencia->id,
            'fecha_inicio' => now()->toDateTimeString(),
            'descripcion_detalle' => 'Retraso en la salida',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.descripcion_detalle', 'Retraso en la salida');
    }

    public function test_incidencia_viaje_api_show(): void
    {
        $this->createTestData();
        $incidencia = IncidenciaViaje::first();

        $response = $this->authenticatedGet("/api/incidenciasviaje/{$incidencia->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $incidencia->id);
    }

    public function test_incidencia_viaje_api_update(): void
    {
        $this->createTestData();
        $incidencia = IncidenciaViaje::first();

        $response = $this->authenticatedPut("/api/incidenciasviaje/{$incidencia->id}", [
            'viaje_id' => $incidencia->viaje_id,
            'tipo_incidencia_id' => $incidencia->tipo_incidencia_id,
            'fecha_inicio' => $incidencia->fecha_inicio,
            'descripcion_detalle' => 'Problema resuelto',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.descripcion_detalle', 'Problema resuelto');
    }

    public function test_incidencia_viaje_api_destroy(): void
    {
        $this->createTestData();
        $incidencia = IncidenciaViaje::first();

        $response = $this->authenticatedDelete("/api/incidenciasviaje/{$incidencia->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) IncidenciaViaje::find($incidencia->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // ASIENTOS DE VIAJE API TESTS
    // ────────────────────────────────────────────────────────

    public function test_asiento_viaje_api_index(): void
    {
        $this->createTestData();

        $response = $this->authenticatedGet('/api/asientosviaje');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'viaje_id', 'numero_asiento', 'estado']
            ],
            'meta'
        ]);
    }

    public function test_asiento_viaje_api_store(): void
    {
        $this->createTestData();
        $viaje = Viaje::first();

        $response = $this->authenticatedPost('/api/asientosviaje', [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '5B',
            'estado' => 'disponible',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.numero_asiento', '5B');
    }

    public function test_asiento_viaje_api_show(): void
    {
        $this->createTestData();
        $asiento = AsientoViaje::first();

        $response = $this->authenticatedGet("/api/asientosviaje/{$asiento->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $asiento->id);
    }

    public function test_asiento_viaje_api_update(): void
    {
        $this->createTestData();
        $asiento = AsientoViaje::first();

        $response = $this->authenticatedPut("/api/asientosviaje/{$asiento->id}", [
            'estado' => 'ocupado',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.estado', 'ocupado');
    }

    public function test_asiento_viaje_api_destroy(): void
    {
        $this->createTestData();
        $asiento = AsientoViaje::first();

        $response = $this->authenticatedDelete("/api/asientosviaje/{$asiento->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) AsientoViaje::find($asiento->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // ASIGNACIONES DE CONDUCTOR API TESTS
    // ────────────────────────────────────────────────────────

    public function test_asignacion_conductor_api_index(): void
    {
        $this->createTestData();

        $response = $this->authenticatedGet('/api/asignacionesconductor');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'viaje_id', 'conductor_id', 'tipo_asignacion']
            ],
            'meta'
        ]);
    }

    public function test_asignacion_conductor_api_store(): void
    {
        $this->createTestData();
        $viaje = Viaje::first();
        $conductor = Conductor::first();

        $response = $this->authenticatedPost('/api/asignacionesconductor', [
            'viaje_id' => $viaje->id,
            'conductor_id' => $conductor->id,
            'tipo_asignacion' => 'ayudante',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.tipo_asignacion', 'ayudante');
    }

    public function test_asignacion_conductor_api_show(): void
    {
        $this->createTestData();
        $asignacion = AsignacionConductor::first();

        $response = $this->authenticatedGet("/api/asignacionesconductor/{$asignacion->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $asignacion->id);
    }

    public function test_asignacion_conductor_api_update(): void
    {
        $this->createTestData();
        $asignacion = AsignacionConductor::first();
        $conductor = Conductor::where('id', '!=', $asignacion->conductor_id)->first() ?? Conductor::create([
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'licencia' => 'LIC-TEST-002',
            'telefono' => '60000000',
            'estado_base' => 1,
        ]);

        $response = $this->authenticatedPut("/api/asignacionesconductor/{$asignacion->id}", [
            'viaje_id' => $asignacion->viaje_id,
            'conductor_id' => $conductor->id,
            'tipo_asignacion' => 'principal',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.tipo_asignacion', 'principal');
    }

    public function test_asignacion_conductor_api_destroy(): void
    {
        $this->createTestData();
        $asignacion = AsignacionConductor::first();

        $response = $this->authenticatedDelete("/api/asignacionesconductor/{$asignacion->id}");

        $response->assertStatus(200);
        $this->assertSame(0, (int) AsignacionConductor::find($asignacion->id)->estado_base);
    }

    // ────────────────────────────────────────────────────────
    // HELPER METHOD: CREATE TEST DATA
    // ────────────────────────────────────────────────────────

    protected function createTestData(): void
    {
        // Crear cliente
        $cliente = Cliente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez García',
            'cedula' => '12345678',
            'email' => 'cliente@test.com',
            'contrasena' => bcrypt('password123'),
            'telefono' => '76543210',
            'estado_base' => 1,
        ]);

        // Crear bus
        $bus = Bus::create([
            'placa' => 'TEST-001',
            'capacidad' => 40,
            'modelo' => '2023',
            'tipo_bus' => 'Interurbano',
            'estado_base' => 1,
        ]);

        // Crear conductor
        $conductor = Conductor::create([
            'nombre' => 'Carlos',
            'apellido' => 'López',
            'licencia' => 'LIC-2023-001',
            'telefono' => '67890123',
            'estado_base' => 1,
        ]);

        // Crear ruta
        $ruta = Ruta::create([
            'nombre_ruta' => 'La Paz - Cochabamba',
            'origen' => 'La Paz',
            'destino' => 'Cochabamba',
            'distancia' => 360,
            'duracion_estimada' => 480,
            'precio_base' => 150.00,
            'estado_base' => 1,
        ]);

        // Crear tipo encomienda
        $tipoEncomienda = TipoEncomienda::create([
            'nombre' => 'Paquete Frágil',
            'descripcion' => 'Paquetes frágiles',
            'precio' => 50.00,
            'estado_base' => 1,
        ]);

        // Crear tipo incidencia
        $tipoIncidencia = TipoIncidencia::create([
            'nombre' => 'Retraso',
            'descripcion' => 'Retrasos en el viaje',
            'nivel_gravedad' => 'Moderado',
            'estado_base' => 1,
        ]);

        // Crear viaje
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => now()->addDays(5)->toDateString(),
            'hora_salida' => '08:00:00',
            'estado' => 'programado',
            'estado_base' => 1,
        ]);

        // Crear asientos
        for ($i = 1; $i <= 5; $i++) {
            AsientoViaje::create([
                'viaje_id' => $viaje->id,
                'numero_asiento' => $i . 'A',
                'estado' => 'disponible',
                'estado_base' => 1,
            ]);
        }

        // Crear reserva
        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now()->toDateString(),
            'cantidad' => 1,
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        // Crear boleto
        Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => 'Juan Pérez',
            'ci_pasajero' => '12345678',
            'telefono_pasajero' => '76543210',
            'precio' => 150.00,
            'metodo_pago' => 'QR',
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        // Crear encomienda
        Encomienda::create([
            'cliente_id' => $cliente->id,
            'remitente_nombre' => 'Juan Pérez',
            'remitente_ci' => '12345678',
            'destinatario_nombre' => 'María García',
            'destinatario_ci' => '87654321',
            'viaje_id' => $viaje->id,
            'tipo_encomienda_id' => $tipoEncomienda->id,
            'cantidad' => 1,
            'total_pagar' => 50.00,
            'estado' => 'recibido',
            'estado_base' => 1,
        ]);

        // Crear notificación
        Notificacion::create([
            'cliente_id' => $cliente->id,
            'titulo' => 'Boleto confirmado',
            'mensaje' => 'Tu boleto ha sido confirmado',
            'tipo' => 'info',
            'canal' => 'sistema',
            'prioridad' => 'normal',
            'estado' => 'pendiente',
            'estado_base' => 1,
        ]);

        // Crear incidencia
        IncidenciaViaje::create([
            'viaje_id' => $viaje->id,
            'tipo_incidencia_id' => $tipoIncidencia->id,
            'fecha_inicio' => now(),
            'descripcion_detalle' => 'Retraso de 30 minutos',
            'estado_base' => 1,
        ]);

        // Crear asignación de conductor
        AsignacionConductor::create([
            'viaje_id' => $viaje->id,
            'conductor_id' => $conductor->id,
            'tipo_asignacion' => 'principal',
            'estado_base' => 1,
        ]);
    }
}
