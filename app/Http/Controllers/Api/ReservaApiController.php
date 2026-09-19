<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Reservas", description: "Gestión de reservas")]
#[OA\Schema(
    schema: "Reserva",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "cliente_id", type: "integer", example: 12),
        new OA\Property(property: "fecha_reserva", type: "string", format: "date-time", example: "2026-09-04T10:00:00Z"),
        new OA\Property(property: "cantidad", type: "integer", example: 2),
        new OA\Property(property: "total_pagar", type: "number", format: "float", example: 240.00),
        new OA\Property(property: "estado", type: "string", example: "pendiente"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class ReservaApiController extends Controller
{
    #[OA\Get(path: "/api/reservas", summary: "Listar reservas", security: [["bearerAuth" => []]], tags: ["Reservas"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por cliente o estado", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de reservas obtenido correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Listado de reservas obtenido correctamente."),
            new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Reserva")),
            new OA\Property(property: "meta", properties: [
                new OA\Property(property: "current_page", type: "integer", example: 1),
                new OA\Property(property: "last_page", type: "integer", example: 2),
                new OA\Property(property: "per_page", type: "integer", example: 15),
                new OA\Property(property: "total", type: "integer", example: 8),
            ], type: "object"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $reservas = Reserva::query()
            ->with('cliente')
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->whereHas('cliente', function ($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%");
                })->orWhere('estado', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de reservas obtenido correctamente.', $reservas, fn (Reserva $reserva) => $this->formatReserva($reserva));
    }

    #[OA\Post(path: "/api/reservas", summary: "Crear una reserva", security: [["bearerAuth" => []]], tags: ["Reservas"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["cliente_id", "cantidad"], properties: [
        new OA\Property(property: "cliente_id", type: "integer", example: 12),
        new OA\Property(property: "cantidad", type: "integer", example: 2),
        new OA\Property(property: "estado", type: "string", example: "pendiente"),
    ])), responses: [
        new OA\Response(response: 201, description: "Reserva creada correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Reserva creada correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Reserva"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cantidad' => 'required|integer|min:1',
            'estado' => 'nullable|in:pendiente,confirmada,cancelada',
        ]);

        $reserva = Reserva::create([ 
            'cliente_id' => $data['cliente_id'],
            'fecha_reserva' => now(),
            'cantidad' => $data['cantidad'],
            'estado' => $data['estado'] ?? 'pendiente',
            'total_pagar' => 0,
        ]);

        return $this->successResponse('Reserva creada correctamente.', $this->formatReserva($reserva), 201);
    }

    #[OA\Get(path: "/api/reservas/{reserva}", summary: "Obtener una reserva por ID", security: [["bearerAuth" => []]], tags: ["Reservas"], parameters: [
        new OA\Parameter(name: "reserva", in: "path", required: true, description: "ID de la reserva", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Reserva obtenida correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Reserva obtenida correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Reserva"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Reserva no encontrada"),
    ])]
    public function show(Reserva $reserva): JsonResponse
    {
        $reserva->load('cliente');

        return $this->successResponse('Reserva obtenida correctamente.', $this->formatReserva($reserva));
    }

    #[OA\Put(path: "/api/reservas/{reserva}", summary: "Actualizar una reserva", security: [["bearerAuth" => []]], tags: ["Reservas"], parameters: [
        new OA\Parameter(name: "reserva", in: "path", required: true, description: "ID de la reserva", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["cliente_id", "cantidad"], properties: [
        new OA\Property(property: "cliente_id", type: "integer", example: 12),
        new OA\Property(property: "cantidad", type: "integer", example: 3),
        new OA\Property(property: "estado", type: "string", example: "confirmada"),
    ])), responses: [
        new OA\Response(response: 200, description: "Reserva actualizada correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Reserva actualizada correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Reserva"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Reserva no encontrada"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, Reserva $reserva): JsonResponse
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cantidad' => 'required|integer|min:1',
            'estado' => 'required|in:pendiente,confirmada,cancelada',
        ]);

        $reserva->update($data);

        return $this->successResponse('Reserva actualizada correctamente.', $this->formatReserva($reserva));
    }

    #[OA\Delete(path: "/api/reservas/{reserva}", summary: "Desactivar una reserva", security: [["bearerAuth" => []]], tags: ["Reservas"], parameters: [
        new OA\Parameter(name: "reserva", in: "path", required: true, description: "ID de la reserva", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Reserva desactivada correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Reserva desactivada correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Reserva"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Reserva no encontrada"),
    ])]
    public function destroy(Reserva $reserva): JsonResponse
    {
        $reserva->update(['estado_base' => 0]);

        return $this->successResponse('Reserva desactivada correctamente.', $this->formatReserva($reserva));
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

    private function formatReserva(Reserva $reserva): array
    {
        return [
            'id' => $reserva->id,
            'cliente_id' => $reserva->cliente_id,
            'fecha_reserva' => $reserva->fecha_reserva?->toISOString(),
            'cantidad' => $reserva->cantidad,
            'total_pagar' => $reserva->total_pagar,
            'estado' => $reserva->estado,
            'estado_base' => (bool) $reserva->estado_base,
            'created_at' => $reserva->created_at?->toISOString(),
            'updated_at' => $reserva->updated_at?->toISOString(),
        ];
    }
}
