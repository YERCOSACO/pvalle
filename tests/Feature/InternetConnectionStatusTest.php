<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternetConnectionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_page_shows_internet_status_without_reception_presence_gate(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'cedula' => '654321',
            'email' => 'otro-cliente@example.test',
            'contrasena' => 'password',
        ]);

        $this->actingAs($cliente, 'cliente')
            ->get(route('cliente.reservas.create'))
            ->assertOk()
            ->assertSee('data-network-status', false)
            ->assertSee('Comprobando conexión...')
            ->assertDontSee('Recepción está disponible')
            ->assertDontSee('data-reception-gate', false);
    }

    public function test_customer_can_start_reservation_without_reception_presence(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'cedula' => '123456',
            'email' => 'cliente@example.test',
            'contrasena' => 'password',
        ]);

        $this->actingAs($cliente, 'cliente')
            ->post(route('cliente.reservas.store'), ['cantidad' => 1])
            ->assertRedirect();

        $this->assertDatabaseCount('reservas', 1);
    }
}
