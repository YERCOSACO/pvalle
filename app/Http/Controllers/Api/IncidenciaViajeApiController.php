<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidenciaViaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Incidencias de Viaje", description: "Gestión de incidencias de viaje")]
#[OA\Schema(
    schema: "IncidenciaViaje",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_incidencia_id", type: "integer", example: 1),
        new OA\Property(property: "fecha_inicio", type: "string", format: "date-time"),
        new OA\Property(property: "fecha_fin", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "descripcion_detalle", type: "string", example: "Retraso en la salida", nullable: true),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class IncidenciaViajeApiController extends Controller
{
    #[OA\Get(path: "/api/incidenciasviaje", summary: "Listar incidencias de viaje", security: [["bearerAuth" => []]], tags: ["Incidencias de Viaje"], parameters: [
        new OA\Parameter(name: "viaje_id", in: "query", required: false, description: "Filtrar por viaje", schema: new OA\Schema(type: "integer")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de incidencias obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $incidencias = IncidenciaViaje::query()
            ->with(['viaje', 'tipoIncidencia'])
            ->where('estado_base', 1)
            ->when($request->viaje_id, function ($query, $viajeId) {
                $query->where('viaje_id', $viajeId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de incidencias obtenido correctamente.', $incidencias, fn (IncidenciaViaje $incidencia) => $this->formatIncidencia($incidencia));
    }

    #[OA\Post(path: "/api/incidenciasviaje", summary: "Crear una incidencia de viaje", security: [["bearerAuth" => []]], tags: ["Incidencias de Viaje"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["viaje_id", "tipo_incidencia_id"], properties: [
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_incidencia_id", type: "integer", example: 1),
        new OA\Property(property: "fecha_inicio", type: "string", format: "date-time"),
        new OA\Property(property: "fecha_fin", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "descripcion_detalle", type: "string", example: "Retraso en la salida", nullable: true),
    ])), responses: [
        new OA\Response(response: 201, description: "Incidencia creada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'tipo_incidencia_id' => 'required|exists:tipo_incidencias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_detalle' => 'nullable|string',
        ]);

        $data['estado_base'] = 1;
        $incidencia = IncidenciaViaje::create($data);
        $incidencia->load(['viaje', 'tipoIncidencia']);

        return $this->successResponse('Incidencia creada correctamente.', $this->formatIncidencia($incidencia), 201);
    }

    #[OA\Get(path: "/api/incidenciasviaje/{incidenciaviaje}", summary: "Obtener una incidencia de viaje por ID", security: [["bearerAuth" => []]], tags: ["Incidencias de Viaje"], parameters: [
        new OA\Parameter(name: "incidenciaviaje", in: "path", required: true, description: "ID de la incidencia", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Incidencia obtenida correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Incidencia no encontrada"),
    ])]
    public function show(IncidenciaViaje $incidenciaviaje): JsonResponse
    {
        $incidenciaviaje->load(['viaje', 'tipoIncidencia']);

        return $this->successResponse('Incidencia obtenida correctamente.', $this->formatIncidencia($incidenciaviaje));
    }

    #[OA\Put(path: "/api/incidenciasviaje/{incidenciaviaje}", summary: "Actualizar una incidencia de viaje", security: [["bearerAuth" => []]], tags: ["Incidencias de Viaje"], parameters: [
        new OA\Parameter(name: "incidenciaviaje", in: "path", required: true, description: "ID de la incidencia", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["viaje_id", "tipo_incidencia_id"], properties: [
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_incidencia_id", type: "integer", example: 1),
        new OA\Property(property: "fecha_inicio", type: "string", format: "date-time"),
        new OA\Property(property: "fecha_fin", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "descripcion_detalle", type: "string", example: "Problema resuelto", nullable: true),
    ])), responses: [
        new OA\Response(response: 200, description: "Incidencia actualizada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Incidencia no encontrada"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, IncidenciaViaje $incidenciaviaje): JsonResponse
    {
        $data = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'tipo_incidencia_id' => 'required|exists:tipo_incidencias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_detalle' => 'nullable|string',
        ]);

        $incidenciaviaje->update($data);

        return $this->successResponse('Incidencia actualizada correctamente.', $this->formatIncidencia($incidenciaviaje));
    }

    #[OA\Delete(path: "/api/incidenciasviaje/{incidenciaviaje}", summary: "Desactivar una incidencia de viaje", security: [["bearerAuth" => []]], tags: ["Incidencias de Viaje"], parameters: [
        new OA\Parameter(name: "incidenciaviaje", in: "path", required: true, description: "ID de la incidencia", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Incidencia desactivada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Incidencia no encontrada"),
    ])]
    public function destroy(IncidenciaViaje $incidenciaviaje): JsonResponse
    {
        $incidenciaviaje->forceFill(['estado_base' => 0])->save();

        return $this->successResponse('Incidencia desactivada correctamente.', $this->formatIncidencia($incidenciaviaje));
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

    private function formatIncidencia(IncidenciaViaje $incidencia): array
    {
        return [
            'id' => $incidencia->id,
            'viaje_id' => $incidencia->viaje_id,
            'tipo_incidencia_id' => $incidencia->tipo_incidencia_id,
            'fecha_inicio' => $incidencia->fecha_inicio?->toISOString(),
            'fecha_fin' => $incidencia->fecha_fin?->toISOString(),
            'descripcion_detalle' => $incidencia->descripcion_detalle,
            'estado_base' => (bool) $incidencia->estado_base,
            'created_at' => $incidencia->created_at?->toISOString(),
            'updated_at' => $incidencia->updated_at?->toISOString(),
        ];
    }
}
