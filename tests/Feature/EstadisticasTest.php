<?php

namespace Tests\Feature;

use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Cliente;
use App\Models\Encomienda;
use App\Models\Reserva;
use App\Models\Ruta;
use App\Models\TipoEncomienda;
use App\Models\User;
use App\Models\Viaje;
use App\Services\KpiService;
use App\Services\Estadisticas\EstadisticasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EstadisticasTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_statistics_open_separate_monthly_details_for_income_routes_and_parcels(): void
    {
        Carbon::setTestNow('2026-10-17 12:00:00');

        $cliente = Cliente::create([
            'nombre' => 'Cliente',
            'apellido' => 'Indicadores',
            'cedula' => '10101010',
            'email' => 'indicadores@example.com',
            'contrasena' => bcrypt('password'),
        ]);
        $ruta = Ruta::create([
            'origen' => 'Santa Cruz',
            'destino' => 'Cochabamba',
            'precio_base' => 50,
        ]);
        $bus = Bus::create(['placa' => 'KPI-001', 'capacidad' => 4]);
        $viajeNormal = $this->crearViaje($ruta, $bus, '2026-10-20', 'programado');
        $viajeRetrasado = $this->crearViaje($ruta, $bus, '2026-10-22', 'retrasado');
        $viajeCancelado = $this->crearViaje($ruta, $bus, '2026-10-23', 'cancelado');
        $viajeAnterior = $this->crearViaje($ruta, $bus, '2026-09-20', 'programado');

        $this->crearBoleto($viajeNormal, $cliente, '1A', 'confirmado', 50, '2026-10-05');
        $this->crearBoleto($viajeRetrasado, $cliente, '1A', 'confirmado', 50, '2026-10-06');
        $this->crearBoleto($viajeRetrasado, $cliente, '1B', 'confirmado', 50, '2026-10-07');
        $this->crearBoleto($viajeNormal, $cliente, '1C', 'confirmado', 25, '2026-09-30', '2026-10-10');
        $this->crearBoleto($viajeNormal, $cliente, '1B', 'pendiente', 90, '2026-10-08');
        $this->crearBoleto($viajeNormal, $cliente, '3A', 'cancelado', 50, '2026-10-11');
        $boletoVencido = $this->crearBoleto($viajeNormal, $cliente, '3B', 'pendiente', 50, '2026-10-12');
        $boletoVencido->forceFill(['expira_en' => '2026-10-12 08:15:00'])->saveQuietly();
        $this->crearBoleto($viajeAnterior, $cliente, '1A', 'confirmado', 40, '2026-09-05');
        $boletoInactivo = $this->crearBoleto($viajeNormal, $cliente, '2A', 'confirmado', 900, '2026-10-09');
        $boletoInactivo->forceFill(['estado_base' => 0])->saveQuietly();

        $tipoEncomienda = TipoEncomienda::create(['nombre' => 'Paquete KPI', 'precio' => 10]);
        $this->crearEncomienda($cliente, $viajeNormal, $tipoEncomienda, 'entregado', '2026-10-03');
        $this->crearEncomienda($cliente, $viajeNormal, $tipoEncomienda, 'entregado', '2026-10-04');
        $this->crearEncomienda($cliente, $viajeNormal, $tipoEncomienda, 'en transito', '2026-10-05');
        $this->crearEncomienda($cliente, $viajeNormal, $tipoEncomienda, 'cancelado', '2026-10-06');
        $this->crearEncomienda($cliente, $viajeAnterior, $tipoEncomienda, 'entregado', '2026-09-06');
        $this->crearEncomienda($cliente, $viajeAnterior, $tipoEncomienda, 'cancelado', '2026-09-07');
        $encomiendaInactiva = $this->crearEncomienda($cliente, $viajeNormal, $tipoEncomienda, 'entregado', '2026-10-10');
        $encomiendaInactiva->forceFill(['estado_base' => 0])->saveQuietly();

        $viajeInactivo = $this->crearViaje($ruta, $bus, '2026-10-24', 'programado');
        $viajeInactivo->forceFill(['estado_base' => 0])->saveQuietly();

        $response = $this->actingAs($this->userWithPermission('estadisticas.ver'))
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('INGRESOS POR BOLETOS')
            ->assertSee('VIAJES POR RUTAS')
            ->assertSee('ENCOMIENDAS')
            ->assertDontSee('INDICADORES DEL NEGOCIO');

        $this->get(route('estadisticas.ingresos', ['mes' => 10, 'anio' => 2026]))
            ->assertOk()
            ->assertSee('Bs 175,00')
            ->assertSee('4 boletos')
            ->assertSee('Santa Cruz → Cochabamba')
            ->assertSee('Métodos de pago')
            ->assertSee('Ingresos por día')
            ->assertSee('Distribución de boletos por método de pago');

        $this->get(route('estadisticas.viajes-ruta', ['mes' => 10, 'anio' => 2026]))
            ->assertOk()
            ->assertSee('Viajes por rutas')
            ->assertSee('Santa Cruz → Cochabamba')
            ->assertSee('4')
            ->assertSee('50,0%')
            ->assertSee('Cancelados')
            ->assertSee('Viajes por día')
            ->assertSee('Distribución de viajes por estado');

        $this->get(route('estadisticas.encomiendas', ['mes' => 10, 'anio' => 2026]))
            ->assertOk()
            ->assertSee('Estadísticas de encomiendas')
            ->assertSee('4')
            ->assertSee('Bs 40,00')
            ->assertSee('Entregado')
            ->assertSee('Santa Cruz → Cochabamba')
            ->assertSee('Encomiendas registradas por día')
            ->assertSee('Distribución de encomiendas por estado');

        $desde = Carbon::create(2026, 10, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();
        $servicio = app(EstadisticasService::class);
        $ingresos = $servicio->detalleIngresos($desde, $hasta);
        $viajes = $servicio->viajesPorRuta($desde, $hasta);
        $encomiendas = $servicio->detalleEncomiendas($desde, $hasta);

        $this->assertSame(175.0, $ingresos['total']);
        $this->assertSame(4, $ingresos['boletos']);
        $this->assertSame(4, $ingresos['por_dia']->sum('boletos'));
        $this->assertSame(3, $viajes['total_viajes']);
        $this->assertSame(8, $viajes['por_ruta']->first()['capacidad']);
        $this->assertSame(50.0, $viajes['por_ruta']->first()['ocupacion']);
        $this->assertSame(4, $encomiendas['cantidad']);
        $this->assertSame(2, $encomiendas['entregadas']);
        $this->assertSame(40.0, $encomiendas['monto']);
        $this->assertSame(4, $encomiendas['por_dia']->sum('cantidad'));

        $servicioKpi = app(KpiService::class);
        $kpisIngresos = $servicioKpi->ingresosPorBoletos($desde, $hasta);
        $kpisViajes = $servicioKpi->viajesPorRutas($desde, $hasta);
        $kpisEncomiendas = $servicioKpi->encomiendas($desde, $hasta);

        $this->assertSame(175.0, $kpisIngresos[0]['valor']);
        $this->assertSame(337.5, $kpisIngresos[0]['variacion']);
        $this->assertSame(50.0, $kpisIngresos[1]['valor']);
        $this->assertSame(87.5, $kpisIngresos[2]['valor']);
        $this->assertSame(66.7, $kpisIngresos[3]['valor']);
        $this->assertSame(3, $kpisViajes[0]['valor']);
        $this->assertSame(200.0, $kpisViajes[0]['variacion']);
        $this->assertSame(50.0, $kpisViajes[1]['valor']);
        $this->assertSame(33.3, $kpisViajes[2]['valor']);
        $this->assertSame(4, $kpisViajes[3]['valor']);
        $this->assertSame(4, $kpisEncomiendas[0]['valor']);
        $this->assertSame(100.0, $kpisEncomiendas[0]['variacion']);
        $this->assertSame(66.7, $kpisEncomiendas[1]['valor']);
        $this->assertSame(1, $kpisEncomiendas[2]['valor']);
        $this->assertSame(30.0, $kpisEncomiendas[3]['valor']);
        $this->assertStringContainsString('14,6%', $kpisEncomiendas[3]['subtitulo']);

        $desdeSeptiembre = Carbon::create(2026, 9, 1)->startOfMonth();
        $hastaSeptiembre = $desdeSeptiembre->copy()->endOfMonth();
        $kpisSeptiembre = $servicioKpi->viajesPorRutas($desdeSeptiembre, $hastaSeptiembre);
        $this->assertSame(1, $kpisSeptiembre[0]['valor']);
        $this->assertNull($kpisSeptiembre[0]['variacion']);

        $this->crearViaje($ruta, $bus, '2026-08-20', 'programado');
        $kpisSeptiembreConComparacion = $servicioKpi->viajesPorRutas($desdeSeptiembre, $hastaSeptiembre);
        $this->assertSame(0.0, $kpisSeptiembreConComparacion[0]['variacion']);
    }

    public function test_statistics_require_their_own_permission_and_validate_period(): void
    {
        $user = $this->userWithPermission('reportes.ver');

        $this->actingAs($user)
            ->get(route('estadisticas.ingresos'))
            ->assertForbidden();
        $this->get(route('estadisticas.viajes-ruta'))->assertForbidden();
        $this->get(route('estadisticas.encomiendas'))->assertForbidden();

        $user->givePermissionTo(Permission::findOrCreate('estadisticas.ver', 'web'));

        $this->get(route('estadisticas.ingresos', ['mes' => 13]))
            ->assertSessionHasErrors('mes');

        $this->get(route('estadisticas.ingresos', ['anio' => 2101]))
            ->assertSessionHasErrors('anio');
    }

    public function test_empty_period_returns_zero_without_division_errors(): void
    {
        $desde = Carbon::create(2026, 10, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();

        $resumen = app(EstadisticasService::class)->resumenMensual($desde, $hasta);

        $this->assertSame(0.0, $resumen['ocupacion']['actual']);
        $this->assertSame(0.0, $resumen['entregas']['actual']);
        $this->assertSame(0.0, $resumen['ingresos']['actual']);
        $this->assertSame(0, $resumen['incidencias_viaje']['actual']);

        $kpis = app(KpiService::class);
        $this->assertNull($kpis->ingresosPorBoletos($desde, $hasta)[0]['valor']);
        $this->assertNull($kpis->ingresosPorBoletos($desde, $hasta)[1]['valor']);
        $this->assertNull($kpis->ingresosPorBoletos($desde, $hasta)[2]['valor']);
        $this->assertNull($kpis->ingresosPorBoletos($desde, $hasta)[3]['valor']);
        $this->assertNull($kpis->viajesPorRutas($desde, $hasta)[0]['variacion']);
        $this->assertNull($kpis->viajesPorRutas($desde, $hasta)[1]['valor']);
        $this->assertNull($kpis->viajesPorRutas($desde, $hasta)[2]['valor']);
        $this->assertNull($kpis->viajesPorRutas($desde, $hasta)[3]['valor']);
        $this->assertNull($kpis->encomiendas($desde, $hasta)[0]['valor']);
        $this->assertNull($kpis->encomiendas($desde, $hasta)[1]['valor']);
        $this->assertNull($kpis->encomiendas($desde, $hasta)[2]['valor']);
        $this->assertNull($kpis->encomiendas($desde, $hasta)[3]['valor']);

        $usuario = $this->userWithPermission('estadisticas.ver');
        foreach (['estadisticas.ingresos', 'estadisticas.viajes-ruta', 'estadisticas.encomiendas'] as $ruta) {
            $respuesta = $this->actingAs($usuario)
                ->get(route($ruta, ['mes' => 8, 'anio' => 2026]))
                ->assertOk()
                ->assertSee('—');

            if ($ruta === 'estadisticas.encomiendas') {
                $respuesta
                    ->assertSee('Sin datos del periodo')
                    ->assertSee('El monto registrado corresponde a las encomiendas no canceladas del periodo; no confirma el cobro. La fecha usada es la de registro.');
            } else {
                $respuesta->assertSee('Sin datos previos');
            }
        }
    }

    private function crearViaje(Ruta $ruta, Bus $bus, string $fecha, string $estado): Viaje
    {
        return Viaje::create([
            'ruta_id' => $ruta->id,
            'bus_id' => $bus->id,
            'fecha_viaje' => $fecha,
            'hora_salida' => '08:00:00',
            'estado' => $estado,
            'estado_base' => 1,
        ]);
    }

    private function crearBoleto(
        Viaje $viaje,
        Cliente $cliente,
        string $asiento,
        string $estado,
        float $precio,
        string $creado,
        ?string $fechaEmision = null
    ): Boleto {
        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'fecha_reserva' => $creado,
            'cantidad' => 1,
            'estado' => $estado === 'confirmado' ? 'confirmada' : 'pendiente',
        ]);
        $boleto = Boleto::create([
            'reserva_id' => $reserva->id,
            'viaje_id' => $viaje->id,
            'numero_asiento' => $asiento,
            'nombre_pasajero' => $cliente->nombre_completo,
            'precio' => $precio,
            'metodo_pago' => 'QR',
            'estado' => $estado,
            'fecha_emision' => $fechaEmision,
            'estado_base' => 1,
        ]);
        $boleto->forceFill(['created_at' => Carbon::parse($creado)])->saveQuietly();

        return $boleto;
    }

    private function crearEncomienda(
        Cliente $cliente,
        Viaje $viaje,
        TipoEncomienda $tipo,
        string $estado,
        string $creado
    ): Encomienda {
        $encomienda = Encomienda::create([
            'cliente_id' => $cliente->id,
            'remitente_nombre' => 'Remitente',
            'destinatario_nombre' => 'Destinatario',
            'viaje_id' => $viaje->id,
            'tipo_encomienda_id' => $tipo->id,
            'cantidad' => 1,
            'total_pagar' => 10,
            'estado' => $estado,
            'estado_base' => 1,
        ]);
        $encomienda->forceFill(['created_at' => Carbon::parse($creado)])->saveQuietly();

        return $encomienda;
    }

    private function userWithPermission(string $permissionName): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $role = Role::findOrCreate("Statistics {$permissionName}", 'web');
        $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        $user->assignRole($role);

        return $user;
    }
}
