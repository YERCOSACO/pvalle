<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionConductor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Asignaciones de Conductor", description: "Gestión de asignaciones de conductores a viajes")]
#[OA\Schema(
    schema: "AsignacionConductor",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "conductor_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_asignacion", type: "string", example: "principal"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class AsignacionConductorApiController extends Controller
{
    #[OA\Get(path: "/api/asignacionesconductor", summary: "Listar asignaciones de conductores", security: [["bearerAuth" => []]], tags: ["Asignaciones de Conductor"], parameters: [
        new OA\Parameter(name: "viaje_id", in: "query", required: false, description: "Filtrar por viaje_id", schema: new OA\Schema(type: "integer")),
        new OA\Parameter(name: "conductor_id", in: "query", required: false, description: "Filtrar por conductor_id", schema: new OA\Schema(type: "integer")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de asignaciones obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $asignaciones = AsignacionConductor::query()
            ->with(['viaje', 'conductor'])
            ->where('estado_base', 1)
            ->when($request->viaje_id, function ($query, $viajeId) {
                $query->where('viaje_id', $viajeId);
            })
            ->when($request->conductor_id, function ($query, $conductorId) {
                $query->where('conductor_id', $conductorId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de asignaciones obtenido correctamente.', $asignaciones, fn (AsignacionConductor $asignacion) => $this->formatAsignacion($asignacion));
    }

    #[OA\Post(path: "/api/asignacionesconductor", summary: "Crear una asignación de conductor", security: [["bearerAuth" => []]], tags: ["Asignaciones de Conductor"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["viaje_id", "conductor_id", "tipo_asignacion"], properties: [
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "conductor_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_asignacion", type: "string", example: "principal"),
    ])), responses: [
        new OA\Response(response: 201, description: "Asignación creada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'conductor_id' => 'required|exists:conductores,id',
            'tipo_asignacion' => 'required|string',
        ]);

        $data['estado_base'] = 1;
        $asignacion = AsignacionConductor::create($data);
        $asignacion->load(['viaje', 'conductor']);

        return $this->successResponse('Asignación creada correctamente.', $this->formatAsignacion($asignacion), 201);
    }

    #[OA\Get(path: "/api/asignacionesconductor/{asignacionconductor}", summary: "Obtener una asignación de conductor por ID", security: [["bearerAuth" => []]], tags: ["Asignaciones de Conductor"], parameters: [
        new OA\Parameter(name: "asignacionconductor", in: "path", required: true, description: "ID de la asignación", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Asignación obtenida correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asignación no encontrada"),
    ])]
    public function show(AsignacionConductor $asignacionconductor): JsonResponse
    {
        $asignacionconductor->load(['viaje', 'conductor']);

        return $this->successResponse('Asignación obtenida correctamente.', $this->formatAsignacion($asignacionconductor));
    }

    #[OA\Put(path: "/api/asignacionesconductor/{asignacionconductor}", summary: "Actualizar una asignación de conductor", security: [["bearerAuth" => []]], tags: ["Asignaciones de Conductor"], parameters: [
        new OA\Parameter(name: "asignacionconductor", in: "path", required: true, description: "ID de la asignación", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["viaje_id", "conductor_id", "tipo_asignacion"], properties: [
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "conductor_id", type: "integer", example: 1),
        new OA\Property(property: "tipo_asignacion", type: "string", example: "principal"),
    ])), responses: [
        new OA\Response(response: 200, description: "Asignación actualizada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asignación no encontrada"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, AsignacionConductor $asignacionconductor): JsonResponse
    {
        $data = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'conductor_id' => 'required|exists:conductores,id',
            'tipo_asignacion' => 'required|string',
        ]);

        $asignacionconductor->update($data);

        return $this->successResponse('Asignación actualizada correctamente.', $this->formatAsignacion($asignacionconductor));
    }

    #[OA\Delete(path: "/api/asignacionesconductor/{asignacionconductor}", summary: "Desactivar una asignación de conductor", security: [["bearerAuth" => []]], tags: ["Asignaciones de Conductor"], parameters: [
        new OA\Parameter(name: "asignacionconductor", in: "path", required: true, description: "ID de la asignación", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Asignación desactivada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asignación no encontrada"),
    ])]
    public function destroy(AsignacionConductor $asignacionconductor): JsonResponse
    {
        $asignacionconductor->forceFill(['estado_base' => 0])->save();

        return $this->successResponse('Asignación desactivada correctamente.', $this->formatAsignacion($asignacionconductor));
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

    private function formatAsignacion(AsignacionConductor $asignacion): array
    {
        return [
            'id' => $asignacion->id,
            'viaje_id' => $asignacion->viaje_id,
            'conductor_id' => $asignacion->conductor_id,
            'tipo_asignacion' => $asignacion->tipo_asignacion,
            'estado_base' => (bool) $asignacion->estado_base,
            'created_at' => $asignacion->created_at?->toISOString(),
            'updated_at' => $asignacion->updated_at?->toISOString(),
        ];
    }
}
