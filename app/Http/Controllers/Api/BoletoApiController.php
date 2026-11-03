<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Reserva;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Boletos", description: "Gestión de boletos")]
#[OA\Schema(
    schema: "Boleto",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "reserva_id", type: "integer", example: 1),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "numero_asiento", type: "string", example: "1A"),
        new OA\Property(property: "nombre_pasajero", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "ci_pasajero", type: "string", example: "12345678"),
        new OA\Property(property: "telefono_pasajero", type: "string", example: "76543210"),
        new OA\Property(property: "precio", type: "number", format: "double", example: 150.50),
        new OA\Property(property: "metodo_pago", type: "string", example: "QR"),
        new OA\Property(property: "estado", type: "string", example: "confirmado"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "fecha_emision", type: "string", format: "date-time"),
        new OA\Property(property: "usuario_id", type: "integer", nullable: true),
        new OA\Property(property: "cliente_emisor_id", type: "integer", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class BoletoApiController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    #[OA\Get(path: "/api/boletos", summary: "Listar boletos", security: [["bearerAuth" => []]], tags: ["Boletos"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por nombre de pasajero", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de boletos obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'sometimes|nullable|string|max:255',
            'page' => 'sometimes|integer|min:1',
        ]);

        $boletos = Boleto::query()
            ->with(['reserva', 'viaje'])
            ->where('estado_base', 1)
            ->when($request->search, function ($query, $search) {
                $query->where('nombre_pasajero', 'like', "%{$search}%")
                    ->orWhere('ci_pasajero', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de boletos obtenido correctamente.', $boletos, fn (Boleto $boleto) => $this->formatBoleto($boleto));
    }

    #[OA\Post(path: "/api/boletos", summary: "Crear un boleto", security: [["bearerAuth" => []]], tags: ["Boletos"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["reserva_id", "viaje_id", "numero_asiento", "nombre_pasajero"], properties: [
        new OA\Property(property: "reserva_id", type: "integer", example: 1),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "numero_asiento", type: "string", example: "1A"),
        new OA\Property(property: "nombre_pasajero", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "ci_pasajero", type: "string", example: "12345678"),
        new OA\Property(property: "telefono_pasajero", type: "string", example: "76543210"),
        new OA\Property(property: "metodo_pago", type: "string", example: "QR"),
        new OA\Property(property: "estado", type: "string", example: "confirmado"),
    ])), responses: [
        new OA\Response(response: 201, description: "Boleto creado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'viaje_id' => 'required|exists:viajes,id',
            'numero_asiento' => ['required', 'string', 'max:4', 'regex:/^\d{1,3}[A-D]$/'],
            'nombre_pasajero' => 'required|string|max:255',
            'ci_pasajero' => 'nullable|string|max:20',
            'telefono_pasajero' => 'nullable|string|max:20',
            'metodo_pago' => 'required|in:QR,efectivo,tarjeta',
            'estado' => 'required|in:pendiente,confirmado',
        ]);

        $boleto = DB::transaction(function () use ($data) {
            $reserva = Reserva::query()
                ->lockForUpdate()
                ->find($data['reserva_id']);
            if (! $reserva || (int) $reserva->estado_base !== 1) {
                throw ValidationException::withMessages([
                    'reserva_id' => 'La reserva seleccionada no está activa.',
                ]);
            }
            $viaje = Viaje::with('ruta')->findOrFail($data['viaje_id']);

            if (! in_array($reserva->estado, ['pendiente', 'confirmada'], true)) {
                throw ValidationException::withMessages([
                    'reserva_id' => 'La reserva no está disponible para emitir boletos.',
                ]);
            }
            $this->asientosService->limpiarExpiradosParaViaje((int) $data['viaje_id']);
            $boletosActivos = $reserva->boletos()
                ->where('estado_base', 1)
                ->where('estado', '!=', 'cancelado')
                ->get();
            if ($boletosActivos->count() >= $reserva->cantidad) {
                throw ValidationException::withMessages([
                    'reserva_id' => 'La reserva ya alcanzó la cantidad de boletos solicitada.',
                ]);
            }
            if ($boletosActivos->contains(fn (Boleto $boleto) => (int) $boleto->viaje_id !== (int) $data['viaje_id'])) {
                throw ValidationException::withMessages([
                    'viaje_id' => 'El viaje debe coincidir con los boletos asociados a la reserva.',
                ]);
            }
            if (($data['estado'] === 'pendiente' && $data['metodo_pago'] !== 'QR')
                || ($data['estado'] === 'confirmado' && $data['metodo_pago'] === 'QR')) {
                throw ValidationException::withMessages([
                    'estado' => 'El estado del boleto debe coincidir con el método de pago seleccionado.',
                ]);
            }
            $this->asientosService->reservar($data['viaje_id'], [$data['numero_asiento']]);

            $data['precio'] = $viaje->ruta->precio_base;
            $data['expira_en'] = $data['estado'] === 'pendiente' && $data['metodo_pago'] === 'QR'
                ? now()->addMinutes(Boleto::TIEMPO_PAGO_QR_MINUTOS)
                : null;
            $data['estado_base'] = 1;
            $data['fecha_emision'] = $data['estado'] === 'confirmado' ? now() : null;
            $data['usuario_id'] = $data['estado'] === 'confirmado' ? auth('api')->id() : null;
            $data['cliente_emisor_id'] = null;

            return Boleto::create($data);
        });
        $boleto->load(['reserva', 'viaje']);

        return $this->successResponse('Boleto creado correctamente.', $this->formatBoleto($boleto), 201);
    }

    #[OA\Get(path: "/api/boletos/{boleto}", summary: "Obtener un boleto por ID", security: [["bearerAuth" => []]], tags: ["Boletos"], parameters: [
        new OA\Parameter(name: "boleto", in: "path", required: true, description: "ID del boleto", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Boleto obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Boleto no encontrado"),
    ])]
    public function show(Boleto $boleto): JsonResponse
    {
        $boleto->load(['reserva', 'viaje']);

        return $this->successResponse('Boleto obtenido correctamente.', $this->formatBoleto($boleto));
    }

    #[OA\Put(path: "/api/boletos/{boleto}", summary: "Actualizar un boleto", security: [["bearerAuth" => []]], tags: ["Boletos"], parameters: [
        new OA\Parameter(name: "boleto", in: "path", required: true, description: "ID del boleto", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre_pasajero", "estado"], properties: [
        new OA\Property(property: "nombre_pasajero", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "ci_pasajero", type: "string", example: "12345678"),
        new OA\Property(property: "telefono_pasajero", type: "string", example: "76543210"),
        new OA\Property(property: "metodo_pago", type: "string", example: "QR"),
        new OA\Property(property: "estado", type: "string", example: "confirmado"),
    ])), responses: [
        new OA\Response(response: 200, description: "Boleto actualizado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Boleto no encontrado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, Boleto $boleto): JsonResponse
    {
        $data = $request->validate([
            'nombre_pasajero' => 'required|string|max:255',
            'ci_pasajero' => 'nullable|string|max:20',
            'telefono_pasajero' => 'nullable|string|max:20',
            'metodo_pago' => 'required|in:QR,efectivo,tarjeta',
            'estado' => 'required|in:pendiente,confirmado,cancelado',
        ]);

        $transicionesPermitidas = [
            'pendiente' => ['pendiente', 'confirmado', 'cancelado'],
            'confirmado' => ['confirmado', 'cancelado'],
            'cancelado' => ['cancelado'],
        ];
        if (! in_array($data['estado'], $transicionesPermitidas[$boleto->estado] ?? [], true)) {
            throw ValidationException::withMessages([
                'estado' => 'La transición solicitada no está permitida para este boleto.',
            ]);
        }
        if ($data['estado'] === 'pendiente' && $data['metodo_pago'] !== 'QR') {
            throw ValidationException::withMessages([
                'estado' => 'Un boleto pendiente debe conservar el método de pago QR.',
            ]);
        }

        if ($boleto->estado === 'pendiente' && $boleto->esta_expirado) {
            $this->asientosService->limpiarExpiradosParaViaje($boleto->viaje_id);

            throw ValidationException::withMessages([
                'estado' => 'El boleto venció y su asiento fue liberado; ya no se puede modificar ni confirmar.',
            ]);
        }
        DB::transaction(function () use ($boleto, $data) {
            $confirmadoAhora = $data['estado'] === 'confirmado' && $boleto->estado !== 'confirmado';
            if ($data['estado'] === 'cancelado' && $boleto->estado !== 'cancelado') {
                $this->asientosService->liberar($boleto->viaje_id, [$boleto->numero_asiento]);
            }

            if ($confirmadoAhora) {
                $data['fecha_emision'] = now();
                $data['usuario_id'] = auth('api')->id();
                $data['cliente_emisor_id'] = null;
            } elseif ($data['estado'] === 'pendiente') {
                $data['fecha_emision'] = null;
                $data['usuario_id'] = null;
            }

            $boleto->update($data);
        });

        return $this->successResponse('Boleto actualizado correctamente.', $this->formatBoleto($boleto));
    }

    #[OA\Delete(path: "/api/boletos/{boleto}", summary: "Desactivar un boleto", security: [["bearerAuth" => []]], tags: ["Boletos"], parameters: [
        new OA\Parameter(name: "boleto", in: "path", required: true, description: "ID del boleto", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Boleto desactivado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Boleto no encontrado"),
    ])]
    public function destroy(Boleto $boleto): JsonResponse
    {
        DB::transaction(function () use ($boleto) {
            $this->asientosService->liberar($boleto->viaje_id, [$boleto->numero_asiento]);
            $boleto->forceFill(['estado_base' => 0])->save();
        });

        return $this->successResponse('Boleto desactivado correctamente.', $this->formatBoleto($boleto));
    }

    private function paginatedResponse(string $message, $paginator, callable $formatter): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->getCollection()->map($formatter)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function successResponse(string $message, array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function formatBoleto(Boleto $boleto): array
    {
        return [
            'id' => $boleto->id,
            'reserva_id' => $boleto->reserva_id,
            'viaje_id' => $boleto->viaje_id,
            'numero_asiento' => $boleto->numero_asiento,
            'nombre_pasajero' => $boleto->nombre_pasajero,
            'ci_pasajero' => $boleto->ci_pasajero,
            'telefono_pasajero' => $boleto->telefono_pasajero,
            'precio' => (float) $boleto->precio,
            'metodo_pago' => $boleto->metodo_pago,
            'estado' => $boleto->estado,
            'estado_base' => (bool) $boleto->estado_base,
            'fecha_emision' => $boleto->fecha_emision?->toISOString(),
            'usuario_id' => $boleto->usuario_id,
            'cliente_emisor_id' => $boleto->cliente_emisor_id,
            'created_at' => $boleto->created_at?->toISOString(),
            'updated_at' => $boleto->updated_at?->toISOString(),
        ];
    }
}
