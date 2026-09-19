<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Transaccional\NotificacionController as TransNotificacionController;
use App\Models\Boleto;
use App\Models\Reserva;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoletoController extends Controller
{
    public function index(Request $request): View
    {
        $cliente = auth('cliente')->user();

        $boletos = Boleto::with('viaje.ruta', 'reserva')
            ->whereHas('reserva', function ($query) use ($cliente) {
                $query->where('cliente_id', $cliente->id);
            })
            ->where('estado_base', 1)
            ->latest()
            ->get();

        $boletosPorViaje = $boletos->groupBy('viaje_id');

        return view('cliente.boletos.index', compact('boletosPorViaje'));
    }

    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    public function create(Reserva $reserva): View
    {
        $cliente = auth('cliente')->user();
        abort_if($reserva->cliente_id !== $cliente->id || $reserva->estado_base !== 1, 404);
        abort_if($reserva->boletos()
            ->where('estado_base', 1)
            ->where('estado', '!=', 'cancelado')
            ->exists(), 404);

        $viajes = Viaje::with('ruta', 'bus')
            ->reservables()
            ->get();

        return view('cliente.boletos.create', compact('reserva', 'viajes'));
    }

    public function asientosDisponibles(Viaje $viaje)
    {
        $viaje = Viaje::reservables()->with('bus', 'ruta')->findOrFail($viaje->id);

        $todos = $this->asientosService->todos($viaje->id);
        $ocupados = $this->asientosService->ocupados($viaje->id);

        return response()->json([
            'todos' => $todos,
            'ocupados' => $ocupados,
            'precio' => $viaje->ruta->precio_base ?? 0,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'viaje_id' => 'required|exists:viajes,id',
            'numero_asiento' => 'required|array|min:1',
            'numero_asiento.*' => 'required|string',
            'tipo_pasajero' => 'required|array|min:1',
            'tipo_pasajero.*' => 'required|in:normal,comodidad,mascota',
            'nombre_pasajero' => 'sometimes|array',
            'nombre_pasajero.*' => 'nullable|string|max:255',
            'ci_pasajero' => 'sometimes|array',
            'ci_pasajero.*' => 'nullable|string|max:50',
            'telefono_pasajero' => 'sometimes|array',
            'telefono_pasajero.*' => 'nullable|string|max:50',
            'metodo_pago' => 'required|in:QR',
        ]);

        $cliente = auth('cliente')->user();
        $reserva = Reserva::where('id', $request->reserva_id)
            ->where('cliente_id', $cliente->id)
            ->where('estado_base', 1)
            ->firstOrFail();

        if ($reserva->boletos()->where('estado_base', 1)->where('estado', '!=', 'cancelado')->exists()) {
            return redirect()->route('cliente.reservas.index')
            ->with('info', 'Esta reserva ya tiene asientos registrados.');
        }

        $viaje = Viaje::reservables()->with('ruta', 'bus')->findOrFail($request->viaje_id);

        $ocupados = $this->asientosService->ocupados($viaje->id);
        $selected = array_unique($request->numero_asiento);

        $asientosValidos = $this->asientosService->todos($viaje->id);
        foreach ($selected as $asiento) {
            if (! in_array($asiento, $asientosValidos, true)) {
                return back()->withErrors(['numero_asiento' => "El asiento {$asiento} no existe en este bus."])->withInput();
            }
        }

        if (count($selected) !== $reserva->cantidad) {
            return back()->withErrors(['numero_asiento' => 'Debes seleccionar exactamente ' . $reserva->cantidad . ' asiento(s).']);
        }

        foreach ($selected as $asiento) {
            if (in_array($asiento, $ocupados)) {
                return back()->withErrors(['numero_asiento' => "El asiento {$asiento} ya está ocupado."]);
            }
        }

        $estado = $request->metodo_pago === 'QR' ? 'pendiente' : 'confirmado';
        $expiraEn = $request->metodo_pago === 'QR' ? Carbon::now()->addMinutes(15) : null;
        $precioBase = $viaje->ruta->precio_base ?? 0;

        DB::transaction(function () use ($selected, $request, $reserva, $viaje, $estado, $expiraEn, $precioBase, $cliente) {
            $this->asientosService->reservar($viaje->id, $selected);

            foreach ($request->numero_asiento as $index => $asiento) {
                $tipo = $request->tipo_pasajero[$index] ?? 'normal';
                $precioFinal = $precioBase;
                if ($tipo === 'comodidad') {
                    $precioFinal += 10;
                } elseif ($tipo === 'mascota') {
                    $precioFinal += 15;
                }

                $nombre = match ($tipo) {
                    'comodidad' => 'Comodidad (espacio extra)',
                    'mascota' => 'Mascota',
                    default => trim((string) $request->input('nombre_pasajero.' . $index, '')) ?: $cliente->nombre_completo,
                };

                $ci = $tipo === 'normal'
                    ? trim((string) $request->input('ci_pasajero.' . $index, '')) ?: $cliente->cedula
                    : null;

                $telefono = $tipo === 'normal'
                    ? trim((string) $request->input('telefono_pasajero.' . $index, '')) ?: $cliente->telefono
                    : null;

                Boleto::create([
                    'reserva_id' => $reserva->id,
                    'viaje_id' => $viaje->id,
                    'numero_asiento' => $asiento,
                    'nombre_pasajero' => $nombre,
                    'ci_pasajero' => $ci,
                    'telefono_pasajero' => $telefono,
                    'espacio_extra' => $tipo === 'comodidad',
                    'mascota' => $tipo === 'mascota',
                    'precio' => $precioFinal,
                    'metodo_pago' => $request->metodo_pago,
                    'estado' => $estado,
                    'expira_en' => $expiraEn,
                    'estado_base' => 1,
                ]);
            }
        });

        $tieneConfirmados = $reserva->boletos()
            ->where('estado_base', 1)
            ->where('estado', 'confirmado')
            ->exists();
        $reserva->update([
            'estado' => $tieneConfirmados ? 'confirmada' : 'pendiente',
        ]);

        TransNotificacionController::crearAutomatica(
            $cliente->id,
            'Boleto creado para tu reserva',
            'Tu reserva fue procesada y ahora puedes ver tu QR de pago en la pantalla.',
            $reserva,
            'info',
            'normal'
        );

        return redirect()->route('cliente.boletos.qr', $reserva)
                         ->with('success', 'Tu QR de pago ya está listo. Usa la pantalla para completar el pago.');
    }

    public function qr(Reserva $reserva): View
    {
        $cliente = auth('cliente')->user();
        abort_if($reserva->cliente_id !== $cliente->id || $reserva->estado_base !== 1, 404);

        $boletos = $reserva->boletos()->with('viaje.ruta')->get();
        $qrImage = asset('images/qr-fake.svg');
        $pendientes = $reserva->boletos()
            ->where('metodo_pago', 'QR')
            ->where('estado', 'pendiente')
            ->where('estado_base', 1)
            ->count();

        return view('cliente.boletos.qr', compact('reserva', 'boletos', 'qrImage', 'pendientes'));
    }

    public function pago(Reserva $reserva): View
    {
        $cliente = auth('cliente')->user();
        abort_if($reserva->cliente_id !== $cliente->id || $reserva->estado_base !== 1, 404);

        $pendientes = $reserva->boletos()
            ->where('metodo_pago', 'QR')
            ->where('estado', 'pendiente')
            ->where('estado_base', 1)
            ->get();

        return view('cliente.boletos.pagar', compact('reserva', 'pendientes'));
    }

    public function confirmarPago(Request $request, Reserva $reserva)
    {
        $cliente = auth('cliente')->user();
        abort_if($reserva->cliente_id !== $cliente->id || $reserva->estado_base !== 1, 404);

        $request->validate([
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $pendientes = $reserva->boletos()
            ->where('metodo_pago', 'QR')
            ->where('estado', 'pendiente')
            ->where('estado_base', 1)
            ->get();

        if ($pendientes->isEmpty()) {
            return redirect()->route('cliente.boletos.index')
                             ->with('info', 'No hay boletos QR pendientes para pagar.');
        }

        $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');

        foreach ($pendientes as $boleto) {
            $boleto->update([
                'comprobante_path' => $comprobantePath,
                'expira_en' => null,
            ]);
        }

        TransNotificacionController::crearAutomatica(
            $cliente->id,
            'Comprobante enviado',
            "Hemos recibido tu comprobante de pago para la reserva #{$reserva->id}. Un administrador validará tu pago.",
            $reserva,
            'info',
            'normal'
        );

        return redirect()->route('cliente.boletos.qr', $reserva)
                         ->with('success', 'Comprobante enviado. El administrador confirmará tu pago.');
    }
}
