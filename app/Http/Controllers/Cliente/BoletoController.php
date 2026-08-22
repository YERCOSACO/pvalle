<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Transaccional\NotificacionController as TransNotificacionController;
use App\Models\Boleto;
use App\Models\Reserva;
use App\Models\Viaje;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        return view('cliente.boletos.index', compact('boletos'));
    }

    private function asientosOcupados(int $viajeId): array
    {
        return Boleto::where('viaje_id', $viajeId)
            ->where('estado_base', 1)
            ->where(function ($query) {
                $query->where('estado', 'confirmado')
                      ->orWhere(function ($q) {
                          $q->where('estado', 'pendiente')
                            ->where('expira_en', '>', now());
                      });
            })
            ->pluck('numero_asiento')
            ->toArray();
    }

    private function generarAsientos(int $capacidad): array
    {
        $asientos = [];
        $letras = ['A', 'B', 'C', 'D'];
        $filas = ceil($capacidad / 4);
        $count = 0;

        foreach (range(1, $filas) as $fila) {
            foreach ($letras as $letra) {
                if ($count >= $capacidad) break;
                $asientos[] = $fila . $letra;
                $count++;
            }
        }

        return $asientos;
    }

    public function create(Reserva $reserva): View
    {
        $cliente = auth('cliente')->user();
        abort_if($reserva->cliente_id !== $cliente->id || $reserva->estado_base !== 1, 404);

        $viajes = Viaje::with('ruta', 'bus')
            ->where('estado_base', 1)
            ->get();

        return view('cliente.boletos.create', compact('reserva', 'viajes'));
    }

    public function asientosDisponibles(Viaje $viaje)
    {
        $viaje->load('bus');

        $todos = $this->generarAsientos($viaje->bus->capacidad);
        $ocupados = $this->asientosOcupados($viaje->id);

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
            'metodo_pago' => 'required|in:QR,efectivo,tarjeta',
        ]);

        $cliente = auth('cliente')->user();
        $reserva = Reserva::where('id', $request->reserva_id)->where('cliente_id', $cliente->id)->firstOrFail();
        $viaje = Viaje::findOrFail($request->viaje_id);

        $ocupados = $this->asientosOcupados($viaje->id);
        $selected = array_unique($request->numero_asiento);

        foreach ($selected as $asiento) {
            if (in_array($asiento, $ocupados)) {
                return back()->withErrors(['numero_asiento' => "El asiento {$asiento} ya está ocupado."]);
            }
        }

        $estado = $request->metodo_pago === 'QR' ? 'pendiente' : 'confirmado';
        $expiraEn = $request->metodo_pago === 'QR' ? Carbon::now()->addMinutes(15) : null;
        $precioBase = $viaje->ruta->precio_base ?? 0;

        foreach ($selected as $asiento) {
            try {
                Boleto::create([
                    'reserva_id' => $reserva->id,
                    'viaje_id' => $viaje->id,
                    'numero_asiento' => $asiento,
                    'nombre_pasajero' => auth('cliente')->user()->nombre_completo,
                    'ci_pasajero' => auth('cliente')->user()->cedula,
                    'telefono_pasajero' => auth('cliente')->user()->telefono,
                    'precio' => $precioBase,
                    'metodo_pago' => $request->metodo_pago,
                    'estado' => $estado,
                    'expira_en' => $expiraEn,
                    'estado_base' => 1,
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                return back()->withErrors(['numero_asiento' => "El asiento {$asiento} ya está ocupado."])->withInput();
            }
        }

        $reserva->update([
            'total_pagar' => $reserva->boletos()->where('estado_base', 1)->sum('precio'),
            'estado' => $reserva->boletos()->count() >= $reserva->cantidad ? 'confirmada' : 'pendiente',
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
        abort_if($reserva->cliente_id !== $cliente->id, 404);

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
        abort_if($reserva->cliente_id !== $cliente->id, 404);

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
        abort_if($reserva->cliente_id !== $cliente->id, 404);

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
