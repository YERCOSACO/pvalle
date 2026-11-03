<?php

namespace Tests\Feature;

use App\Models\AsientoViaje;
use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AsientoViajeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seat_management_is_available_in_parametrization_and_can_search(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje();
        $ultimoViaje = Viaje::create([
            'ruta_id' => $viaje->ruta_id,
            'bus_id' => $viaje->bus_id,
            'fecha_viaje' => '2026-10-30',
            'hora_salida' => '21:30:00',
            'estado' => 'programado',
        ]);
        $asiento = AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
        ]);

        $gestion = $this->actingAs($user)
            ->get(route('parametrizacion.asientoviaje.gestion', ['search' => 'TEST-2']))
            ->assertOk()
            ->assertSee('Administrar asientos por bus')
            ->assertSee('ADMINISTRAR ASIENTOS DE VIAJE')
            ->assertSee('TEST-2')
            ->assertSee('Buscar')
            ->assertSee('Ruta')
            ->assertSee('Santa Cruz - Vallegrande')
            ->assertSee('>Viaje</th>', false)
            ->assertSee('Ver maqueta')
            ->assertDontSee('Ver maqueta →')
            ->assertDontSee('Agregar asiento');
        $this->assertStringContainsString(
            route('parametrizacion.asientoviaje.viaje', $ultimoViaje),
            $gestion->getContent()
        );
        $this->assertStringNotContainsString(
            route('parametrizacion.asientoviaje.viaje', $viaje),
            $gestion->getContent()
        );

        $this->get(route('parametrizacion.asientoviaje.viaje', $viaje))
            ->assertOk()
            ->assertSee('Bus TEST-2')
            ->assertSee('Santa Cruz → Vallegrande')
            ->assertSee('1A')
            ->assertSee('Faltante')
            ->assertSee('Modificar')
            ->assertSee('Eliminar')
            ->assertDontSee('Agregar asiento')
            ->assertDontSee('Agregar</span>')
            ->assertDontSee('Viaje que deseas revisar')
            ->assertDontSee('Asientos según capacidad')
            ->assertDontSee('Asientos adicionales')
            ->assertSee('flex flex-row items-stretch', false)
            ->assertSee('overflow-x-auto', false)
            ->assertSee('scrollbar-subtle', false)
            ->assertSee('max-h-[220px] space-y-2 overflow-y-auto', false)
            ->assertSee('Asientos registrados', false)
            ->assertSee('grid-cols-5', false)
            ->assertSee('grid-column: 1;', false)
            ->assertSee('grid-column: 2;', false);

        $this->post(route('parametrizacion.asientoviaje.store'), [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1B',
            'return_to' => 'viaje',
        ])->assertRedirect(route('parametrizacion.asientoviaje.viaje', $viaje));

        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '9D',
            'estado' => 'disponible',
        ]);

        $this->get(route('parametrizacion.asientoviaje.viaje', $viaje))
            ->assertOk()
            ->assertSee('9D')
            ->assertSee('1B');

        $this->get(route('parametrizacion.asientoviaje.create'))
            ->assertOk()
            ->assertSee('Agregar asiento de viaje')
            ->assertSee('Seleccione un bus')
            ->assertSee('Ver viajes del bus');

        $this->get(route('parametrizacion.asientoviaje.edit', [
            'asientoviaje' => $asiento,
            'return_to' => 'gestion',
        ]))
            ->assertOk()
            ->assertSee('Editar Asiento 1A')
            ->assertSee('name="return_to" value="gestion"', false);
    }

    public function test_add_form_selects_a_bus_before_showing_only_its_trips(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje();
        $otroViaje = $this->createViaje(capacidad: 3);

        $this->actingAs($user)
            ->get(route('parametrizacion.asientoviaje.create'))
            ->assertOk()
            ->assertSee('TEST-2')
            ->assertSee('TEST-3')
            ->assertDontSee('name="viaje_id"', false);

        $response = $this->get(route('parametrizacion.asientoviaje.create', ['bus_id' => $viaje->bus_id]));
        $response
            ->assertOk()
            ->assertSee('name="viaje_id"', false)
            ->assertSee('value="' . $viaje->id . '"', false)
            ->assertSee('TEST-2');

        preg_match('/<select id="viaje_id".*?<\/select>/s', $response->getContent(), $tripSelect);
        $this->assertNotEmpty($tripSelect);
        $this->assertStringNotContainsString('value="' . $otroViaje->id . '"', $tripSelect[0]);
    }

    public function test_seat_map_loads_data_for_the_requested_trip_instead_of_another_trip_of_the_same_bus(): void
    {
        $user = $this->userWithSeatPermissions();
        $primerViaje = $this->createViaje();
        $segundoViaje = Viaje::create([
            'ruta_id' => $primerViaje->ruta_id,
            'bus_id' => $primerViaje->bus_id,
            'fecha_viaje' => '2026-10-21',
            'hora_salida' => '21:00:00',
            'estado' => 'programado',
        ]);
        AsientoViaje::create([
            'viaje_id' => $primerViaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
        ]);
        AsientoViaje::create([
            'viaje_id' => $segundoViaje->id,
            'numero_asiento' => '1B',
            'estado' => 'disponible',
        ]);

        $response = $this->actingAs($user)
            ->get(route('parametrizacion.asientoviaje.viaje', $segundoViaje))
            ->assertOk()
            ->assertSee('21/10/2026')
            ->assertSee('1B');

        $response->assertDontSee('name="viaje_id"', false)
            ->assertDontSee('Agregar asiento');
    }

    public function test_seats_can_be_added_only_within_the_bus_capacity_and_reactivated_after_deletion(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje(capacidad: 2);
        $this->actingAs($user);

        $this->post(route('parametrizacion.asientoviaje.store'), [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1b',
        ])->assertRedirect(route('parametrizacion.asientoviaje.gestion'));

        $asiento = AsientoViaje::where('viaje_id', $viaje->id)
            ->where('numero_asiento', '1B')
            ->firstOrFail();
        $this->assertSame(1, (int) $asiento->estado_base);
        $this->assertSame('disponible', $asiento->estado);

        $this->delete(route('parametrizacion.asientoviaje.destroy', $asiento))
            ->assertRedirect(route('parametrizacion.asientoviaje.gestion'));
        $this->assertSame(0, (int) $asiento->fresh()->estado_base);

        $this->post(route('parametrizacion.asientoviaje.store'), [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1B',
        ])->assertRedirect(route('parametrizacion.asientoviaje.gestion'));
        $this->assertSame(1, (int) $asiento->fresh()->estado_base);

        $this->from(route('parametrizacion.asientoviaje.create'))
            ->post(route('parametrizacion.asientoviaje.store'), [
                'viaje_id' => $viaje->id,
                'numero_asiento' => '3A',
            ])
            ->assertSessionHasErrors('numero_asiento');
    }

    public function test_a_seat_with_a_current_ticket_cannot_be_modified_or_deleted(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje(capacidad: 1);
        $asiento = AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'ocupado',
        ]);
        $cliente = Cliente::create([
            'nombre' => 'Pasajero',
            'apellido' => 'Prueba',
            'cedula' => '12345678',
            'email' => 'pasajero.prueba@example.com',
            'contrasena' => bcrypt('password'),
        ]);
        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'confirmada',
        ]);
        Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => 'Pasajero Prueba',
            'precio' => 60,
            'metodo_pago' => 'efectivo',
            'estado' => 'confirmado',
        ]);

        $this->actingAs($user)
            ->delete(route('parametrizacion.asientoviaje.destroy', $asiento))
            ->assertSessionHasErrors('asiento');
        $this->assertSame(1, (int) $asiento->fresh()->estado_base);

        $this->put(route('parametrizacion.asientoviaje.update', $asiento), [
            'estado' => 'disponible',
        ])->assertSessionHasErrors('estado');
        $this->assertSame('ocupado', $asiento->fresh()->estado);
    }

    public function test_blocked_seats_are_shown_and_rejected_by_staff_and_customer_ticket_flows(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje(capacidad: 2);
        $bloqueado = AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
        ]);
        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1B',
            'estado' => 'disponible',
        ]);

        $this->actingAs($user)
            ->put(route('parametrizacion.asientoviaje.update', $bloqueado), [
                'estado' => 'bloqueado',
                'return_to' => 'viaje',
            ])
            ->assertRedirect(route('parametrizacion.asientoviaje.viaje', $viaje));

        $this->get(route('transaccional.boletos.asientos-disponibles', $viaje))
            ->assertOk()
            ->assertJsonPath('bloqueados', ['1A'])
            ->assertJsonPath('disponibles', ['1B']);

        $this->get(route('parametrizacion.asientoviaje.viaje', $viaje))
            ->assertOk()
            ->assertSee('Bloqueado')
            ->assertSee('bg-slate-400', false);

        $this->get(route('transaccional.boletos.por-viaje', $viaje))
            ->assertOk()
            ->assertSee('Bloqueados')
            ->assertSee('bg-slate-400 border-slate-600', false)
            ->assertSee('title="Asiento bloqueado"', false);

        $this->get(route('parametrizacion.asientoviaje.show', $viaje))
            ->assertOk()
            ->assertSee('Bloqueados')
            ->assertSee('bg-slate-400 border-slate-600', false);

        $this->get(route('parametrizacion.asientoviaje.imprimir', $viaje))
            ->assertOk()
            ->assertSee('Bloqueados')
            ->assertSee('bloqueado');

        $reservaStaff = Reserva::create([
            'cliente_id' => $this->createCliente()->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
        ]);

        $this->from(route('transaccional.boletos.create'))
            ->post(route('transaccional.boletos.store'), [
                'reserva_id' => $reservaStaff->id,
                'viaje_id' => $viaje->id,
                'numero_asiento' => ['1A'],
                'tipo_pasajero' => ['normal'],
                'nombre_pasajero' => ['Pasajero bloqueado'],
                'metodo_pago' => 'efectivo',
            ])
            ->assertSessionHasErrors([
                'numero_asiento' => 'El asiento 1A está bloqueado y no se puede reservar.',
            ]);
        $this->assertDatabaseMissing('boletos', [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
        ]);

        $cliente = $this->createCliente();
        $reservaCliente = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'pendiente',
        ]);

        $this->actingAs($cliente, 'cliente')
            ->get(route('cliente.boletos.create', $reservaCliente))
            ->assertOk()
            ->assertSee('Bloqueado')
            ->assertSee('bg-slate-400 border-slate-600', false);

        $this->actingAs($cliente, 'cliente')
            ->get(route('cliente.boletos.asientos-disponibles', $viaje))
            ->assertOk()
            ->assertJsonPath('bloqueados', ['1A']);

        $this->post(route('cliente.boletos.store'), [
            'reserva_id' => $reservaCliente->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => ['1A'],
            'tipo_pasajero' => ['normal'],
            'metodo_pago' => 'QR',
        ])->assertSessionHasErrors([
            'numero_asiento' => 'El asiento 1A está bloqueado y no se puede reservar.',
        ]);
        $this->assertDatabaseMissing('boletos', [
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
        ]);

        try {
            app(ViajeAsientosService::class)->reservar($viaje->id, ['1A']);
            $this->fail('Un asiento bloqueado no debe poder reservarse.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertStringContainsString('está bloqueado', $exception->errors()['numero_asiento'][0]);
        }

        $this->assertSame('bloqueado', $bloqueado->fresh()->estado);
    }

    public function test_staff_ticket_validation_requires_distinct_formatted_seats_and_aligned_passenger_data(): void
    {
        $user = $this->userWithSeatPermissions();
        $viaje = $this->createViaje(capacidad: 2);
        foreach (['1A', '1B'] as $numeroAsiento) {
            AsientoViaje::create([
                'viaje_id' => $viaje->id,
                'numero_asiento' => $numeroAsiento,
                'estado' => 'disponible',
            ]);
        }
        $reserva = Reserva::create([
            'cliente_id' => $this->createCliente()->id,
            'fecha_reserva' => now(),
            'cantidad' => 2,
            'estado' => 'pendiente',
        ]);
        $this->actingAs($user);

        $this->post(route('transaccional.boletos.store'), [
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => ['1A'],
            'tipo_pasajero' => ['normal'],
            'nombre_pasajero' => ['Pasajero válido'],
            'metodo_pago' => 'efectivo',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('boletos', [
            'reserva_id' => $reserva->id,
            'numero_asiento' => '1A',
            'estado' => 'confirmado',
        ]);

        $this->from(route('transaccional.boletos.create'))
            ->post(route('transaccional.boletos.store'), [
                'reserva_id' => $reserva->id,
                'viaje_id' => $viaje->id,
                'numero_asiento' => ['1B', '1C'],
                'tipo_pasajero' => ['normal'],
                'nombre_pasajero' => ['Primer pasajero', 'Segundo pasajero'],
                'metodo_pago' => 'QR',
            ])
            ->assertSessionHasErrors('tipo_pasajero');

        $this->from(route('transaccional.boletos.create'))
            ->post(route('transaccional.boletos.store'), [
                'reserva_id' => $reserva->id,
                'viaje_id' => $viaje->id,
                'numero_asiento' => ['invalid'],
                'tipo_pasajero' => ['normal'],
                'nombre_pasajero' => ['Pasajero'],
                'metodo_pago' => 'QR',
            ])
            ->assertSessionHasErrors('numero_asiento.0');

        $this->from(route('transaccional.boletos.create'))
            ->post(route('transaccional.boletos.store'), [
                'reserva_id' => $reserva->id,
                'viaje_id' => $viaje->id,
                'numero_asiento' => ['1B'],
                'tipo_pasajero' => ['normal'],
                'metodo_pago' => 'QR',
            ])
            ->assertSessionHasErrors('nombre_pasajero.0');
    }

    private function userWithSeatPermissions(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();

        $role = Role::findOrCreate('Administrar asiento viaje test', 'web');
        foreach ([
            'asientoviaje.ver',
            'asientoviaje.crear',
            'asientoviaje.editar',
            'asientoviaje.eliminar',
            'boletos.ver',
            'boletos.crear',
        ] as $name) {
            $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }
        $user->assignRole($role);

        return $user;
    }

    private function createCliente(): Cliente
    {
        return Cliente::create([
            'nombre' => 'Pasajero',
            'apellido' => 'Prueba',
            'cedula' => uniqid('CI'),
            'email' => uniqid('pasajero') . '@example.com',
            'contrasena' => bcrypt('password'),
        ]);
    }

    private function createViaje(int $capacidad = 2): Viaje
    {
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Vallegrande',
            'precio_base' => 60,
        ]);
        $bus = Bus::create([
            'placa' => 'TEST-' . $capacidad,
            'capacidad' => $capacidad,
        ]);

        return Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-10-20',
            'hora_salida' => '20:00:00',
            'estado' => 'programado',
        ]);
    }
}
