<?php

namespace Tests\Feature;

use App\Models\AsientoViaje;
use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Cliente;
use App\Models\Encomienda;
use App\Models\Reserva;
use App\Models\Ruta;
use App\Models\TipoEncomienda;
use App\Models\User;
use App\Models\Viaje;
use App\Services\Reporte\ReporteBoletoService;
use App\Services\Reporte\ReporteViajeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    public function test_viaje_and_encomienda_reports_render_on_screen_and_for_printing(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('reportes.ver', 'web'));
        $user->assignRole($role);

        $this->actingAs($user);

        $this->get(route('reporte.boletos'))
            ->assertOk()
            ->assertSee('Reporte de boletos')
            ->assertSee('Reporte de Encomiendas');

        $desde = '2026-09-01';
        $hasta = '2026-09-30';
        foreach ([
            route('reporte.boletos', compact('desde', 'hasta')),
            route('reporte.boletos', ['desde' => $desde, 'hasta' => $hasta, 'imprimir' => 1]),
            route('reporte.viajes', compact('desde', 'hasta')),
            route('reporte.viajes', ['desde' => $desde, 'hasta' => $hasta, 'imprimir' => 1]),
            route('reporte.encomiendas', compact('desde', 'hasta')),
            route('reporte.encomiendas', ['desde' => $desde, 'hasta' => $hasta, 'imprimir' => 1]),
        ] as $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->status(), 'Redirected to ' . $response->headers->get('Location'));
        }

        $this->get(route('reporte.viajes', compact('desde', 'hasta')))
            ->assertSee('01/09/2026')
            ->assertSee('30/09/2026');
    }

    public function test_ticket_and_trip_reports_show_newest_records_first(): void
    {
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Vallegrande',
            'precio_base' => 60,
        ]);
        $bus = Bus::create(['placa' => 'ORD-001', 'capacidad' => 2]);

        $viajes = [];
        foreach ([
            ['2026-10-01 10:00:00', '2026-10-08'],
            ['2026-10-02 10:00:00', '2026-10-07'],
        ] as [$createdAt, $fechaViaje]) {
            $viaje = Viaje::create([
                'ruta_id' => $ruta->id,
                'bus_id' => $bus->id,
                'fecha_viaje' => $fechaViaje,
                'hora_salida' => '20:00:00',
                'estado' => 'programado',
            ]);
            $viaje->forceFill(['created_at' => $createdAt])->saveQuietly();
            $viajes[] = $viaje;
        }

        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Orden',
            'cedula' => '88001122',
            'email' => 'cliente.orden@example.com',
            'contrasena' => bcrypt('password'),
            'estado_base' => 1,
        ]);

        $boletos = [];
        foreach ($viajes as $index => $viaje) {
            $reserva = Reserva::create([
                'cliente_id' => $cliente->id,
                'fecha_reserva' => now(),
                'cantidad' => 1,
                'estado' => 'confirmada',
            ]);
            $boleto = Boleto::create([
                'reserva_id' => $reserva->id,
                'viaje_id' => $viaje->id,
                'numero_asiento' => '1' . chr(65 + $index),
                'nombre_pasajero' => 'Pasajero ' . ($index + 1),
                'precio' => 60,
                'metodo_pago' => 'efectivo',
                'estado' => 'confirmado',
                'estado_base' => 1,
            ]);
            $boleto->forceFill(['created_at' => $index === 0 ? '2026-10-01 11:00:00' : '2026-10-02 11:00:00'])
                ->saveQuietly();
            $boletos[] = $boleto;
        }

        $boletosRecientesPrimero = app(ReporteBoletoService::class)
            ->boletosPorRangoFechas('2026-10-01', '2026-10-31')['boletos']
            ->modelKeys();
        $viajesRecientesPrimero = app(ReporteViajeService::class)
            ->viajesPorRangoFechas('2026-10-01', '2026-10-31')['viajes']
            ->modelKeys();

        $this->assertSame([$boletos[1]->id, $boletos[0]->id], $boletosRecientesPrimero);
        $this->assertSame([$viajes[1]->id, $viajes[0]->id], $viajesRecientesPrimero);
    }

    public function test_trip_reports_validate_the_selected_trip(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('reportes.ver', 'web'));
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('reporte.viajes', ['viaje_id' => 'no-es-un-id']))
            ->assertSessionHasErrors('viaje_id');

        $this->get(route('reporte.encomiendas', ['viaje_id' => 999999]))
            ->assertSessionHasErrors('viaje_id');
    }

    public function test_search_and_trip_filters_work_across_the_three_reports(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte filtros test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('reportes.ver', 'web'));
        $user->assignRole($role);

        $rutaUno = Ruta::create(['origen' => 'Santa Cruz', 'destino' => 'Vallegrande', 'precio_base' => 60]);
        $rutaDos = Ruta::create(['origen' => 'La Paz', 'destino' => 'Cochabamba', 'precio_base' => 70]);
        $busUno = Bus::create(['placa' => 'BUS-UNO', 'capacidad' => 2]);
        $busDos = Bus::create(['placa' => 'BUS-DOS', 'capacidad' => 2]);
        $viajeUno = Viaje::create([
            'ruta_id' => $rutaUno->id,
            'bus_id' => $busUno->id,
            'fecha_viaje' => '2026-10-08',
            'hora_salida' => '20:00:00',
            'estado' => 'programado',
        ]);
        $viajeDos = Viaje::create([
            'ruta_id' => $rutaDos->id,
            'bus_id' => $busDos->id,
            'fecha_viaje' => '2026-10-08',
            'hora_salida' => '21:00:00',
            'estado' => 'programado',
        ]);
        $cliente = Cliente::create([
            'nombre' => 'María',
            'apellido' => 'Reportes',
            'cedula' => '76543210',
            'email' => 'maria.reportes@example.com',
            'contrasena' => bcrypt('password'),
            'estado_base' => 1,
        ]);
        $reservaUno = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'confirmada',
        ]);
        $reservaDos = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'confirmada',
        ]);
        Boleto::create([
            'reserva_id' => $reservaUno->id,
            'viaje_id' => $viajeUno->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => 'Pasajera Encontrada',
            'ci_pasajero' => '11112222',
            'precio' => 60,
            'metodo_pago' => 'efectivo',
            'estado' => 'confirmado',
            'estado_base' => 1,
            'created_at' => '2026-10-08 10:00:00',
            'updated_at' => '2026-10-08 10:00:00',
        ]);
        Boleto::create([
            'reserva_id' => $reservaDos->id,
            'viaje_id' => $viajeDos->id,
            'numero_asiento' => '1B',
            'nombre_pasajero' => 'Pasajero Diferente',
            'ci_pasajero' => '33334444',
            'precio' => 70,
            'metodo_pago' => 'efectivo',
            'estado' => 'confirmado',
            'estado_base' => 1,
            'created_at' => '2026-10-08 11:00:00',
            'updated_at' => '2026-10-08 11:00:00',
        ]);
        $tipo = TipoEncomienda::create(['nombre' => 'Caja especial', 'precio' => 25]);
        $encomiendaUno = Encomienda::create([
            'remitente_nombre' => 'Remitente Uno',
            'destinatario_nombre' => 'Destino Encontrado',
            'viaje_id' => $viajeUno->id,
            'tipo_encomienda_id' => $tipo->id,
            'cantidad' => 1,
            'total_pagar' => 25,
            'estado' => 'recibido',
            'created_at' => '2026-10-08 10:00:00',
            'updated_at' => '2026-10-08 10:00:00',
        ]);
        Encomienda::create([
            'remitente_nombre' => 'Remitente Dos',
            'destinatario_nombre' => 'Destino Diferente',
            'viaje_id' => $viajeDos->id,
            'tipo_encomienda_id' => $tipo->id,
            'cantidad' => 1,
            'total_pagar' => 25,
            'estado' => 'recibido',
            'created_at' => '2026-10-08 11:00:00',
            'updated_at' => '2026-10-08 11:00:00',
        ]);

        $this->actingAs($user);
        $periodo = ['desde' => '2026-10-01', 'hasta' => '2026-10-31'];

        $this->get(route('reporte.boletos', $periodo + ['buscar' => 'Encontrada']))
            ->assertOk()
            ->assertSee('Pasajera Encontrada')
            ->assertDontSee('Pasajero Diferente')
            ->assertSee('name="buscar"', false);

        $reporteViajes = $this->get(route('reporte.viajes', $periodo + ['viaje_id' => $viajeUno->id, 'buscar' => 'BUS-UNO']))
            ->assertOk()
            ->assertSee('Santa Cruz')
            ->assertSee('name="viaje_id"', false)
            ->assertSee('name="buscar"', false)
            ->assertSee('value="' . $viajeUno->id . '" selected', false);
        $this->assertSame(1, substr_count($reporteViajes->getContent(), 'class="transition hover:bg-slate-50"'));

        $this->get(route('reporte.encomiendas', $periodo + ['viaje_id' => $viajeUno->id, 'buscar' => 'Encontrado']))
            ->assertOk()
            ->assertSee('Destino Encontrado')
            ->assertDontSee('Destino Diferente')
            ->assertSee('name="viaje_id"', false)
            ->assertSee('name="buscar"', false);

        $this->get(route('reporte.encomiendas', $periodo + [
            'viaje_id' => $viajeUno->id,
            'buscar' => 'Encontrado',
            'imprimir' => 1,
        ]))
            ->assertOk()
            ->assertSee('Destino Encontrado')
            ->assertDontSee('Destino Diferente');
    }

    public function test_report_rejects_a_reversed_date_range(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('reportes.ver', 'web'));
        $user->assignRole($role);

        $this->actingAs($user);

        foreach (['boletos', 'viajes', 'encomiendas'] as $reporte) {
            $this->get(route('reporte.' . $reporte, [
                'desde' => '2026-09-30',
                'hasta' => '2026-09-01',
            ]))->assertSessionHasErrors('hasta');
        }
    }

    public function test_seat_report_list_accepts_a_date_range(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Asientos reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('asientoviaje.ver', 'web'));
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('parametrizacion.asientoviaje.index', [
                'desde' => '2026-09-01',
                'hasta' => '2026-09-30',
            ]))
            ->assertOk()
            ->assertSee('Reporte de asientos por viaje')
            ->assertSee('Buscar viajes');
    }

    public function test_seat_report_can_be_viewed_and_printed_for_a_trip(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Asientos reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('asientoviaje.ver', 'web'));
        $user->assignRole($role);

        $ruta = Ruta::create(['origen' => 'Santa Cruz', 'destino' => 'Vallegrande', 'precio_base' => 60]);
        $bus = Bus::create(['placa' => 'TST-001', 'capacidad' => 1]);
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-09-29',
            'hora_salida' => '20:00:00',
            'estado' => 'programado',
        ]);
        AsientoViaje::create([
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'estado' => 'disponible',
        ]);

        $otraRuta = Ruta::create(['origen' => 'Vallegrande', 'destino' => 'Santa Cruz', 'precio_base' => 60]);
        $otroViaje = Viaje::create([
            'ruta_id' => $otraRuta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-09-30',
            'hora_salida' => '21:00:00',
            'estado' => 'programado',
        ]);
        AsientoViaje::create([
            'viaje_id' => $otroViaje->id,
            'numero_asiento' => '2A',
            'estado' => 'disponible',
        ]);

        $this->actingAs($user)
            ->get(route('parametrizacion.asientoviaje.show', $viaje))
            ->assertOk()
            ->assertSee('1A')
            ->assertDontSee('2A')
            ->assertSee('Total asientos');

        $this->get(route('parametrizacion.asientoviaje.imprimir', $viaje))
            ->assertOk()
            ->assertSee('Reporte de asientos')
            ->assertSee('Santa Cruz → Vallegrande')
            ->assertDontSee('2A')
            ->assertSee('images/logo.png');

        $this->get(route('parametrizacion.asientoviaje.imprimir', [
            'asientoviaje' => $viaje,
            'auto_print' => 1,
        ]))
            ->assertOk()
            ->assertSee('window.print()');
    }

    public function test_encomienda_report_defaults_to_the_whole_current_year(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 8)->startOfDay());

        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte test', 'web');
        $role->givePermissionTo(Permission::findOrCreate('reportes.ver', 'web'));
        $user->assignRole($role);

        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Vallegrande',
            'precio_base' => 60,
        ]);
        $bus = Bus::create(['placa' => 'REP-001', 'capacidad' => 1]);
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-08-20',
            'hora_salida' => '20:00:00',
            'estado' => 'programado',
        ]);
        $tipo = TipoEncomienda::create([
            'nombre' => 'Paquete',
            'precio' => 25,
        ]);
        $encomienda = Encomienda::create([
            'remitente_nombre' => 'Remitente de prueba',
            'destinatario_nombre' => 'Destinatario histórico',
            'viaje_id' => $viaje->id,
            'tipo_encomienda_id' => $tipo->id,
            'cantidad' => 1,
            'total_pagar' => 25,
            'estado' => 'recibido',
            'created_at' => '2026-08-21 10:00:00',
            'updated_at' => '2026-08-21 10:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('reporte.encomiendas'))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-12-31"', false)
            ->assertSee('Destinatario histórico')
            ->assertSee('1 registros')
            ->assertSee(route('transaccional.encomiendas.imprimir', $encomienda), false)
            ->assertSee('Ver / imprimir');

        $this->get(route('transaccional.encomiendas.imprimir', $encomienda))
            ->assertOk()
            ->assertSee('BOLETO DE ENCOMIENDA')
            ->assertSee('ENV-' . str_pad((string) $encomienda->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('Destinatario histórico')
            ->assertSee('window.print()');

        $this->travelBack();
    }

    public function test_ticket_report_links_each_row_to_its_printable_ticket(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['name' => 'Operador de prueba'])->save();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate('Reporte test', 'web');
        $role->givePermissionTo([
            Permission::findOrCreate('reportes.ver', 'web'),
            Permission::findOrCreate('boletos.editar', 'web'),
        ]);
        $user->assignRole($role);
        $this->travelTo(Carbon::parse('2026-10-08 20:15:00', 'UTC'));

        $cliente = Cliente::create([
            'nombre' => 'Pasajero',
            'apellido' => 'Reporte',
            'cedula' => '99887766',
            'email' => 'pasajero.reporte@example.com',
            'contrasena' => bcrypt('password'),
            'estado_base' => 1,
        ]);
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Vallegrande',
            'precio_base' => 60,
        ]);
        $bus = Bus::create(['placa' => 'TKT-001', 'capacidad' => 1]);
        $viaje = Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => '2026-10-10',
            'hora_salida' => '20:00:00',
            'estado' => 'programado',
        ]);
        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => now(),
            'cantidad' => 1,
            'estado' => 'confirmada',
        ]);
        $boleto = Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => '1A',
            'nombre_pasajero' => 'Pasajero de prueba',
            'precio' => 60,
            'metodo_pago' => 'efectivo',
            'estado' => 'pendiente',
            'estado_base' => 1,
            'expira_en' => now()->addMinutes(15),
            'fecha_emision' => null,
            'usuario_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('reporte.boletos'))
            ->assertOk()
            ->assertSee(route('transaccional.boletos.imprimir', $boleto), false)
            ->assertSee('Ver / imprimir');

        $this->get(route('transaccional.boletos.imprimir', $boleto))
            ->assertOk()
            ->assertSee('BOL-' . str_pad((string) $boleto->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('Pasajero de prueba')
            ->assertSee('Pasajero Reporte')
            ->assertSee('Pendiente de confirmación')
            ->assertSee('Aún no confirmado')
            ->assertDontSee('Copia generada');

        $this->patch(route('transaccional.boletos.confirmar-pago', $boleto))
            ->assertRedirect();

        $this->assertDatabaseHas('boletos', [
            'id' => $boleto->id,
            'estado' => 'confirmado',
            'fecha_emision' => '2026-10-08 20:15:00',
            'usuario_id' => $user->id,
            'cliente_emisor_id' => null,
        ]);

        $this->get(route('transaccional.boletos.imprimir', $boleto))
            ->assertOk()
            ->assertSee('Operador de prueba')
            ->assertSee('Confirmado por')
            ->assertSee('Fecha y hora')
            ->assertSee('08/10/2026 16:15:00')
            ->assertDontSee('Copia generada');
    }

    public function test_printable_ticket_requires_an_authenticated_user(): void
    {
        $this->get(route('transaccional.boletos.imprimir', 1))
            ->assertRedirect(route('login'));
    }
}
