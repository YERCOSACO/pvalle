<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoIncidencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Tipos de incidencia", description: "Gestión de tipos de incidencia")]
#[OA\Schema(
    schema: "TipoIncidencia",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Retraso"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Retraso por lluvia"),
        new OA\Property(property: "nivel_gravedad", type: "string", example: "Moderado"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class TipoIncidenciaApiController extends Controller
{
    #[OA\Get(path: "/api/tipoincidencias", summary: "Listar tipos de incidencia", security: [["bearerAuth" => []]], tags: ["Tipos de incidencia"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por nombre", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de tipos de incidencia", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Listado de tipos de incidencia obtenido correctamente."),
            new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/TipoIncidencia")),
            new OA\Property(property: "meta", properties: [
                new OA\Property(property: "current_page", type: "integer", example: 1),
                new OA\Property(property: "last_page", type: "integer", example: 2),
                new OA\Property(property: "per_page", type: "integer", example: 15),
                new OA\Property(property: "total", type: "integer", example: 5),
            ], type: "object"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $tipos = TipoIncidencia::query()
            ->activos()
            ->when($request->search, fn ($query, $search) => $query->where('nombre', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de tipos de incidencia obtenido correctamente.', $tipos, fn (TipoIncidencia $tipo) => $this->formatTipoIncidencia($tipo));
    }

    #[OA\Post(path: "/api/tipoincidencias", summary: "Crear un tipo de incidencia", security: [["bearerAuth" => []]], tags: ["Tipos de incidencia"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "nivel_gravedad"], properties: [
        new OA\Property(property: "nombre", type: "string", example: "Retraso"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Retraso por lluvia"),
        new OA\Property(property: "nivel_gravedad", type: "string", example: "Moderado"),
    ])), responses: [
        new OA\Response(response: 201, description: "Tipo de incidencia creado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de incidencia creado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoIncidencia"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel_gravedad' => 'required|string|max:50',
        ]);

        $tipo = TipoIncidencia::create($data);

        return $this->successResponse('Tipo de incidencia creado correctamente.', $this->formatTipoIncidencia($tipo), 201);
    }

    #[OA\Get(path: "/api/tipoincidencias/{tipoincidencia}", summary: "Obtener un tipo de incidencia por ID", security: [["bearerAuth" => []]], tags: ["Tipos de incidencia"], parameters: [
        new OA\Parameter(name: "tipoincidencia", in: "path", required: true, description: "ID del tipo de incidencia", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Tipo de incidencia obtenido correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de incidencia obtenido correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoIncidencia"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de incidencia no encontrado"),
    ])]
    public function show(TipoIncidencia $tipoincidencia): JsonResponse
    {
        return $this->successResponse('Tipo de incidencia obtenido correctamente.', $this->formatTipoIncidencia($tipoincidencia));
    }

    #[OA\Put(path: "/api/tipoincidencias/{tipoincidencia}", summary: "Actualizar un tipo de incidencia", security: [["bearerAuth" => []]], tags: ["Tipos de incidencia"], parameters: [
        new OA\Parameter(name: "tipoincidencia", in: "path", required: true, description: "ID del tipo de incidencia", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["nombre", "nivel_gravedad"], properties: [
        new OA\Property(property: "nombre", type: "string", example: "Retraso"),
        new OA\Property(property: "descripcion", type: "string", nullable: true, example: "Retraso por lluvia"),
        new OA\Property(property: "nivel_gravedad", type: "string", example: "Grave"),
    ])), responses: [
        new OA\Response(response: 200, description: "Tipo de incidencia actualizado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de incidencia actualizado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoIncidencia"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de incidencia no encontrado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, TipoIncidencia $tipoincidencia): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel_gravedad' => 'required|string|max:50',
        ]);

        $tipoincidencia->update($data);

        return $this->successResponse('Tipo de incidencia actualizado correctamente.', $this->formatTipoIncidencia($tipoincidencia));
    }

    #[OA\Delete(path: "/api/tipoincidencias/{tipoincidencia}", summary: "Desactivar un tipo de incidencia", security: [["bearerAuth" => []]], tags: ["Tipos de incidencia"], parameters: [
        new OA\Parameter(name: "tipoincidencia", in: "path", required: true, description: "ID del tipo de incidencia", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Tipo de incidencia desactivado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Tipo de incidencia desactivado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/TipoIncidencia"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Tipo de incidencia no encontrado"),
    ])]
    public function destroy(TipoIncidencia $tipoincidencia): JsonResponse
    {
        $tipoincidencia->update(['estado_base' => 0]);

        return $this->successResponse('Tipo de incidencia desactivado correctamente.', $this->formatTipoIncidencia($tipoincidencia));
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

    private function formatTipoIncidencia(TipoIncidencia $tipo): array
    {
        return [
            'id' => $tipo->id,
            'nombre' => $tipo->nombre,
            'descripcion' => $tipo->descripcion,
            'nivel_gravedad' => $tipo->nivel_gravedad,
            'estado_base' => (bool) $tipo->estado_base,
            'created_at' => $tipo->created_at?->toISOString(),
            'updated_at' => $tipo->updated_at?->toISOString(),
        ];
    }
}
