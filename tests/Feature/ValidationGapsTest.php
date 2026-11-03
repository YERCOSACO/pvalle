<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Cliente;
use App\Models\IncidenciaViaje;
use App\Models\Notificacion;
use App\Models\Ruta;
use App\Models\TipoEncomienda;
use App\Models\TipoIncidencia;
use App\Models\User;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ValidationGapsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_origin_must_be_a_matching_existing_source_record(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Create notification validation test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('notificaciones.crear', 'web'));
        $user->assignRole($role);

        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'cedula' => '987654321',
            'email' => 'cliente.validation@example.com',
            'contrasena' => bcrypt('password'),
        ]);
        $ruta = Ruta::create([
            'origen' => 'Origen',
            'destino' => 'Destino',
            'precio_base' => 10,
        ]);
        $bus = Bus::create([
            'placa' => 'VALID-01',
            'capacidad' => 4,
        ]);
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => now()->addDay()->toDateString(),
            'hora_salida' => '10:00:00',
            'estado' => 'programado',
        ]);
        $tipo = TipoIncidencia::create([
            'nombre' => 'Retraso',
            'nivel_gravedad' => 'Leve',
        ]);
        $incidencia = IncidenciaViaje::create([
            'viaje_id' => $viaje->id,
            'tipo_incidencia_id' => $tipo->id,
            'fecha_inicio' => now(),
            'descripcion_detalle' => 'Retraso breve',
        ]);

        $payload = [
            'cliente_id' => $cliente->id,
            'titulo' => 'Aviso',
            'mensaje' => 'Información del viaje.',
            'tipo' => 'info',
            'canal' => 'sistema',
            'prioridad' => 'normal',
            'origen_tipo' => 'incidencia',
        ];

        $this->actingAs($user)
            ->from(route('transaccional.notificaciones.create'))
            ->post(route('transaccional.notificaciones.store'), $payload + ['origen_id' => 999999])
            ->assertSessionHasErrors('origen_id');

        $this->post(route('transaccional.notificaciones.store'), $payload + ['origen_id' => $incidencia->id])
            ->assertRedirect(route('transaccional.notificaciones.index'));

        $notificacion = Notificacion::firstOrFail();
        $this->assertSame(IncidenciaViaje::class, $notificacion->referenciable_type);
        $this->assertSame($incidencia->id, $notificacion->referenciable_id);
    }

    public function test_confirm_password_rejects_missing_password_with_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('password.confirm'))
            ->post(route('password.confirm'))
            ->assertRedirect(route('password.confirm'))
            ->assertSessionHasErrors('password');
    }

    public function test_confirm_password_still_accepts_the_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($user)
            ->post(route('password.confirm'), ['password' => 'correct-password'])
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_customer_reservation_create_validates_query_parameters_and_keeps_valid_defaults(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Reserva',
            'cedula' => '123450987',
            'email' => 'reserva.validation@example.com',
            'contrasena' => bcrypt('password'),
        ]);

        $this->actingAs($cliente, 'cliente')
            ->get(route('cliente.reservas.create', ['viaje_id' => 'invalid']))
            ->assertSessionHasErrors('viaje_id');

        $this->get(route('cliente.reservas.create', ['pasajeros' => 0]))
            ->assertSessionHasErrors('pasajeros');

        $this->get(route('cliente.reservas.create', ['pasajeros' => 2]))
            ->assertOk()
            ->assertSee('name="cantidad"', false);
    }

    public function test_route_form_rejects_amounts_that_exceed_database_precision(): void
    {
        $user = $this->userWithPermission('rutas.crear');

        $this->actingAs($user)
            ->from(route('parametrizacion.rutas.create'))
            ->post(route('parametrizacion.rutas.store'), [
                'origen' => 'Santa Cruz',
                'destino' => 'Cochabamba',
                'distancia_km' => '1000000.00',
                'precio_base' => '120.999',
            ])
            ->assertRedirect(route('parametrizacion.rutas.create'))
            ->assertSessionHasErrors(['distancia_km', 'precio_base']);
    }

    public function test_trip_form_rejects_malformed_departure_time_and_inactive_bus(): void
    {
        $user = $this->userWithPermission('viajes.crear');
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Cochabamba',
            'precio_base' => 120,
            'estado_base' => 1,
        ]);
        $bus = Bus::create([
            'placa' => 'INACTIVE-01',
            'capacidad' => 20,
        ]);
        $bus->forceFill(['estado_base' => 0])->save();

        $this->actingAs($user)
            ->from(route('transaccional.viajes.create'))
            ->post(route('transaccional.viajes.store'), [
                'ruta_id' => $ruta->id,
                'bus_id' => $bus->id,
                'fecha_viaje' => now()->addDay()->toDateString(),
                'hora_salida' => 'no-es-hora',
            ])
            ->assertRedirect(route('transaccional.viajes.create'))
            ->assertSessionHasErrors(['bus_id', 'hora_salida']);
    }

    public function test_role_form_rejects_permission_names_not_in_the_system(): void
    {
        $user = $this->userWithPermission('roles.crear');

        $this->actingAs($user)
            ->post(route('seguridad.roles.store'), [
                'name' => 'Rol válido de prueba',
                'permisos' => ['roles.crear'],
            ])
            ->assertRedirect(route('seguridad.roles.index'));

        $this->actingAs($user)
            ->from(route('seguridad.roles.create'))
            ->post(route('seguridad.roles.store'), [
                'name' => 'Otro rol de prueba',
                'permisos' => ['permiso.inexistente'],
            ])
            ->assertRedirect(route('seguridad.roles.create'))
            ->assertSessionHasErrors('permisos.0');
    }

    public function test_encomienda_form_rejects_a_total_that_exceeds_decimal_storage(): void
    {
        $user = $this->userWithPermission('encomiendas.crear');
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Cochabamba',
            'precio_base' => 120,
        ]);
        $bus = Bus::create([
            'placa' => 'ENCOMIENDA-01',
            'capacidad' => 20,
        ]);
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => now()->addDay()->toDateString(),
            'hora_salida' => '08:00',
        ]);
        $tipo = TipoEncomienda::create([
            'nombre' => 'Paquete costoso',
            'precio' => 999999.99,
        ]);

        $this->actingAs($user)
            ->from(route('transaccional.encomiendas.create'))
            ->post(route('transaccional.encomiendas.store'), [
                'remitente_nombre' => 'Remitente',
                'destinatario_nombre' => 'Destinatario',
                'viaje_id' => $viaje->id,
                'tipo_encomienda_id' => $tipo->id,
                'cantidad' => 2,
            ])
            ->assertRedirect(route('transaccional.encomiendas.create'))
            ->assertSessionHasErrors('cantidad');
    }

    public function test_bus_form_rejects_capacity_above_the_seat_generation_safety_limit(): void
    {
        $user = $this->userWithPermission('buses.crear');

        $this->actingAs($user)
            ->from(route('parametrizacion.buses.create'))
            ->post(route('parametrizacion.buses.store'), [
                'placa' => 'CAPACITY-OVER-LIMIT',
                'capacidad' => 1001,
            ])
            ->assertRedirect(route('parametrizacion.buses.create'))
            ->assertSessionHasErrors('capacidad');
    }

    public function test_customer_registration_rejects_a_future_date_of_birth(): void
    {
        $this->from(route('cliente.register'))
            ->post(route('cliente.register.store'), [
                'nombre' => 'Cliente',
                'apellido' => 'Prueba',
                'cedula' => '9988776655',
                'email' => 'future.dob@example.com',
                'contrasena' => 'Secure-Pass-2026!',
                'contrasena_confirmation' => 'Secure-Pass-2026!',
                'fecha_nacimiento' => now()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('cliente.register'))
            ->assertSessionHasErrors('fecha_nacimiento');
    }

    private function userWithPermission(string $permissionName): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate("Validation {$permissionName}", 'web');
        $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        $user->assignRole($role);

        return $user;
    }
}
