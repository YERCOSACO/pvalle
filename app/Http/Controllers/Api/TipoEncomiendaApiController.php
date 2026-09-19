<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoEncomienda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Tipos de encomienda", description: "Gestión de tipos de encomienda")]
#[OA\Schema(
    schema: "TipoEncomienda",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Bolsa"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Paquete pequeño"),
        new OA\Property(property: "precio", type: "number", format: "float", example: 15.00),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class TipoEncomiendaApiController extends Controller
{
    #[OA\Get(path: "/api/tipoencomiendas", summary: "Listar tipos de encomienda", security: [["bearerAuth" => []]], tags: ["Tipos de encomienda"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por nombre", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de tipos de encomienda", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Listado de tipos de encomienda obtenido correctamente."),
            new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/TipoEncomienda")),
            new OA\Property(property: "meta", properties: [
                new OA\Property(property: "current_page", type: "integer", example: 1),
                new OA\Property(property: "last_page", type: "integer", example: 2),
                new OA\Property(property: "per_page", type: "integer", example: 15),
                new OA\Property(property: "total", type: "integer", example: 6),
            ], type: "object"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $tipos = TipoEncomienda::query()
            ->activos()
            ->when($request->search, fn ($query, $search) => $query->where('nombre', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de tipos de encomienda obtenido correctamente.', $tipos, fn (TipoEncomienda $tipo) => $this->formatTipoEncomienda($tipo));
    }

    #[OA\Post(path: "/api/tipoencomiendas", summary: "Crear un tipo de encomienda", security: [["bearerAuth" => []]], tags: ["Tipos de encomienda"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "precio"], properties: [
        new OA\Property(property: "nombre", type: "string", example: "Bolsa"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Paquete pequeño"),
        new OA\Property(property: "precio", type: "number", format: "float", example: 15.00),
    ])), responses: [
        new OA\Response(response: 201, description: "Tipo de encomienda creado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de encomienda creado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoEncomienda"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
        ]);

        $tipo = TipoEncomienda::create($data);

        return $this->successResponse('Tipo de encomienda creado correctamente.', $this->formatTipoEncomienda($tipo), 201);
    }

    #[OA\Get(path: "/api/tipoencomiendas/{tipoencomienda}", summary: "Obtener un tipo de encomienda por ID", security: [["bearerAuth" => []]], tags: ["Tipos de encomienda"], parameters: [
        new OA\Parameter(name: "tipoencomienda", in: "path", required: true, description: "ID del tipo de encomienda", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Tipo de encomienda obtenido correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de encomienda obtenido correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoEncomienda"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de encomienda no encontrado"),
    ])]
    public function show(TipoEncomienda $tipoencomienda): JsonResponse
    {
        return $this->successResponse('Tipo de encomienda obtenido correctamente.', $this->formatTipoEncomienda($tipoencomienda));
    }

    #[OA\Put(path: "/api/tipoencomiendas/{tipoencomienda}", summary: "Actualizar un tipo de encomienda", security: [["bearerAuth" => []]], tags: ["Tipos de encomienda"], parameters: [
        new OA\Parameter(name: "tipoencomienda", in: "path", required: true, description: "ID del tipo de encomienda", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "precio"], properties: [
        new OA\Property(property: "nombre", type: "string", example: "Bolsa"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Paquete pequeño"),
        new OA\Property(property: "precio", type: "number", format: "float", example: 18.00),
    ])), responses: [
        new OA\Response(response: 200, description: "Tipo de encomienda actualizado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de encomienda actualizado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoEncomienda"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de encomienda no encontrado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, TipoEncomienda $tipoencomienda): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
        ]);

        $tipoencomienda->update($data);

        return $this->successResponse('Tipo de encomienda actualizado correctamente.', $this->formatTipoEncomienda($tipoencomienda));
    }

    #[OA\Delete(path: "/api/tipoencomiendas/{tipoencomienda}", summary: "Desactivar un tipo de encomienda", security: [["bearerAuth" => []]], tags: ["Tipos de encomienda"], parameters: [
        new OA\Parameter(name: "tipoencomienda", in: "path", required: true, description: "ID del tipo de encomienda", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Tipo de encomienda desactivado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de encomienda desactivado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoEncomienda"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de encomienda no encontrado"),
    ])]
    public function destroy(TipoEncomienda $tipoencomienda): JsonResponse
    {
        $tipoencomienda->update(['estado_base' => 0]);

        return $this->successResponse('Tipo de encomienda desactivado correctamente.', $this->formatTipoEncomienda($tipoencomienda));
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

    private function formatTipoEncomienda(TipoEncomienda $tipo): array
    {
        return [
            'id' => $tipo->id,
            'nombre' => $tipo->nombre,
            'descripcion' => $tipo->descripcion,
            'precio' => $tipo->precio,
            'estado_base' => (bool) $tipo->estado_base,
            'created_at' => $tipo->created_at?->toISOString(),
            'updated_at' => $tipo->updated_at?->toISOString(),
        ];
    }
}
