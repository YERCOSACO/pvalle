<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Services\ReceptionAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReceptionAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_a_recent_receptionist_heartbeat_counts_as_available(): void
    {
        $role = Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);
        $receptionist = User::factory()->create();
        $receptionist->assignRole($role);
        $availability = app(ReceptionAvailability::class);

        $this->assertFalse($availability->isAvailable());

        $availability->heartbeat();
        $this->assertTrue($availability->isAvailable());

        $this->travel(46)->seconds();
        $this->assertFalse($availability->isAvailable());
    }

    public function test_only_receptionists_can_send_presence_heartbeats(): void
    {
        $receptionistRole = Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);
        $receptionist = User::factory()->create();
        $receptionist->assignRole($receptionistRole);

        $this->actingAs($receptionist)
            ->post(route('recepcion.presencia'))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $sellerRole = Role::create(['name' => 'Vendedor', 'guard_name' => 'web']);
        $seller = User::factory()->create();
        $seller->assignRole($sellerRole);

        $this->actingAs($seller)
            ->post(route('recepcion.presencia'))
            ->assertForbidden();
    }

    public function test_customer_reservation_is_blocked_without_reception_and_allowed_with_reception(): void
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
            ->assertSessionHasErrors('recepcion');

        $this->assertDatabaseCount('reservas', 0);

        $role = Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);
        $receptionist = User::factory()->create();
        $receptionist->assignRole($role);
        app(ReceptionAvailability::class)->heartbeat();

        $this->actingAs($cliente, 'cliente')
            ->post(route('cliente.reservas.store'), ['cantidad' => 1])
            ->assertRedirect();

        $this->assertDatabaseCount('reservas', 1);
    }

    public function test_reservation_page_displays_phone_instructions_without_reception(): void
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
            ->assertSee('No podemos confirmar la disponibilidad de recepción en línea en este momento.')
            ->assertSee('tel:59171005452', false)
            ->assertSee('data-network-status', false)
            ->assertSee('Comprobando conexión...')
            ->assertSee('disabled', false);
    }
}
