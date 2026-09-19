<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encomienda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Encomiendas", description: "Gestión de encomiendas")]
#[OA\Schema(
    schema: "Encomienda",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "cliente_id", type: "integer", example: 1),
        new OA\Property(property: "remitente_nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "remitente_ci", type: "string", example: "12345678"),
        new OA\Property(property: "remitente_telefono", type: "string", example: "76543210"),
        new OA\Property(property: "destinatario_nombre", type: "string", example: "María García"),
        new OA\Property(property: "destinatario_ci", type: "string", example: "87654321"),
        new OA\Property(property: "destinatario_telefono", type: "string", example: "67890123"),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_encomienda_id", type: "integer", example: 1),
        new OA\Property(property: "cantidad", type: "integer", example: 1),
        new OA\Property(property: "total_pagar", type: "number", format: "double", example: 50.00),
        new OA\Property(property: "estado", type: "string", example: "recibido"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class EncomiendaApiController extends Controller
{
    #[OA\Get(path: "/api/encomiendas", summary: "Listar encomiendas", security: [["bearerAuth" => []]], tags: ["Encomiendas"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por destinatario", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de encomiendas obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $encomiendas = Encomienda::query()
            ->with(['cliente', 'viaje', 'tipoEncomienda'])
            ->where('estado_base', 1)
            ->when($request->search, function ($query, $search) {
                $query->where('destinatario_nombre', 'like', "%{$search}%")
                    ->orWhere('remitente_nombre', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de encomiendas obtenido correctamente.', $encomiendas, fn (Encomienda $encomienda) => $this->formatEncomienda($encomienda));
    }

    #[OA\Post(path: "/api/encomiendas", summary: "Crear una encomienda", security: [["bearerAuth" => []]], tags: ["Encomiendas"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["destinatario_nombre", "viaje_id", "tipo_encomienda_id", "cantidad"], properties: [
        new OA\Property(property: "cliente_id", type: "integer", example: 1),
        new OA\Property(property: "remitente_nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "remitente_ci", type: "string", example: "12345678"),
        new OA\Property(property: "remitente_telefono", type: "string", example: "76543210"),
        new OA\Property(property: "destinatario_nombre", type: "string", example: "María García"),
        new OA\Property(property: "destinatario_ci", type: "string", example: "87654321"),
        new OA\Property(property: "destinatario_telefono", type: "string", example: "67890123"),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_encomienda_id", type: "integer", example: 1),
        new OA\Property(property: "cantidad", type: "integer", example: 1),
        new OA\Property(property: "total_pagar", type: "number", format: "double", example: 50.00),
    ])), responses: [
        new OA\Response(response: 201, description: "Encomienda creada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'remitente_nombre' => 'nullable|string|max:255',
            'remitente_ci' => 'nullable|string|max:20',
            'remitente_telefono' => 'nullable|string|max:20',
            'destinatario_nombre' => 'required|string|max:255',
            'destinatario_ci' => 'nullable|string|max:20',
            'destinatario_telefono' => 'nullable|string|max:20',
            'viaje_id' => 'required|exists:viajes,id',
            'tipo_encomienda_id' => 'required|exists:tipo_encomiendas,id',
            'cantidad' => 'required|integer|min:1',
            'total_pagar' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string',
        ]);

        $data['estado_base'] = 1;
        $data['estado'] = $data['estado'] ?? 'recibido';
        $encomienda = Encomienda::create($data);
        $encomienda->load(['cliente', 'viaje', 'tipoEncomienda']);

        return $this->successResponse('Encomienda creada correctamente.', $this->formatEncomienda($encomienda), 201);
    }

    #[OA\Get(path: "/api/encomiendas/{encomienda}", summary: "Obtener una encomienda por ID", security: [["bearerAuth" => []]], tags: ["Encomiendas"], parameters: [
        new OA\Parameter(name: "encomienda", in: "path", required: true, description: "ID de la encomienda", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Encomienda obtenida correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Encomienda no encontrada"),
    ])]
    public function show(Encomienda $encomienda): JsonResponse
    {
        $encomienda->load(['cliente', 'viaje', 'tipoEncomienda']);

        return $this->successResponse('Encomienda obtenida correctamente.', $this->formatEncomienda($encomienda));
    }

    #[OA\Put(path: "/api/encomiendas/{encomienda}", summary: "Actualizar una encomienda", security: [["bearerAuth" => []]], tags: ["Encomiendas"], parameters: [
        new OA\Parameter(name: "encomienda", in: "path", required: true, description: "ID de la encomienda", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["destinatario_nombre"], properties: [
        new OA\Property(property: "remitente_nombre", type: "string", example: "Juan Pérez"),
        new OA\Property(property: "remitente_ci", type: "string", example: "12345678"),
        new OA\Property(property: "remitente_telefono", type: "string", example: "76543210"),
        new OA\Property(property: "destinatario_nombre", type: "string", example: "María García"),
        new OA\Property(property: "destinatario_ci", type: "string", example: "87654321"),
        new OA\Property(property: "destinatario_telefono", type: "string", example: "67890123"),
        new OA\Property(property: "cantidad", type: "integer", example: 1),
        new OA\Property(property: "total_pagar", type: "number", format: "double", example: 50.00),
        new OA\Property(property: "estado", type: "string", example: "recibido"),
    ])), responses: [
        new OA\Response(response: 200, description: "Encomienda actualizada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Encomienda no encontrada"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, Encomienda $encomienda): JsonResponse
    {
        $data = $request->validate([
            'remitente_nombre' => 'nullable|string|max:255',
            'remitente_ci' => 'nullable|string|max:20',
            'remitente_telefono' => 'nullable|string|max:20',
            'destinatario_nombre' => 'required|string|max:255',
            'destinatario_ci' => 'nullable|string|max:20',
            'destinatario_telefono' => 'nullable|string|max:20',
            'cantidad' => 'required|integer|min:1',
            'total_pagar' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string',
        ]);

        $encomienda->update($data);

        return $this->successResponse('Encomienda actualizada correctamente.', $this->formatEncomienda($encomienda));
    }

    #[OA\Delete(path: "/api/encomiendas/{encomienda}", summary: "Desactivar una encomienda", security: [["bearerAuth" => []]], tags: ["Encomiendas"], parameters: [
        new OA\Parameter(name: "encomienda", in: "path", required: true, description: "ID de la encomienda", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Encomienda desactivada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Encomienda no encontrada"),
    ])]
    public function destroy(Encomienda $encomienda): JsonResponse
    {
        $encomienda->forceFill(['estado_base' => 0])->save();

        return $this->successResponse('Encomienda desactivada correctamente.', $this->formatEncomienda($encomienda));
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

    private function formatEncomienda(Encomienda $encomienda): array
    {
        return [
            'id' => $encomienda->id,
            'cliente_id' => $encomienda->cliente_id,
            'remitente_nombre' => $encomienda->remitente_nombre,
            'remitente_ci' => $encomienda->remitente_ci,
            'remitente_telefono' => $encomienda->remitente_telefono,
            'destinatario_nombre' => $encomienda->destinatario_nombre,
            'destinatario_ci' => $encomienda->destinatario_ci,
            'destinatario_telefono' => $encomienda->destinatario_telefono,
            'viaje_id' => $encomienda->viaje_id,
            'tipo_encomienda_id' => $encomienda->tipo_encomienda_id,
            'cantidad' => $encomienda->cantidad,
            'total_pagar' => (float) $encomienda->total_pagar,
            'estado' => $encomienda->estado,
            'estado_base' => (bool) $encomienda->estado_base,
            'created_at' => $encomienda->created_at?->toISOString(),
            'updated_at' => $encomienda->updated_at?->toISOString(),
        ];
    }
}
