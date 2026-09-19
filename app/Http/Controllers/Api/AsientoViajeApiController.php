<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsientoViaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Asientos de Viaje", description: "Gestión de asientos de viaje")]
#[OA\Schema(
    schema: "AsientoViaje",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "numero_asiento", type: "string", example: "1A"),
        new OA\Property(property: "estado", type: "string", example: "disponible"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class AsientoViajeApiController extends Controller
{
    #[OA\Get(path: "/api/asientosviaje", summary: "Listar asientos de viaje", security: [["bearerAuth" => []]], tags: ["Asientos de Viaje"], parameters: [
        new OA\Parameter(name: "viaje_id", in: "query", required: false, description: "Filtrar por viaje_id", schema: new OA\Schema(type: "integer")),
        new OA\Parameter(name: "estado", in: "query", required: false, description: "Filtrar por estado", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de asientos obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $asientos = AsientoViaje::query()
            ->with('viaje')
            ->where('estado_base', 1)
            ->when($request->viaje_id, function ($query, $viajeId) {
                $query->where('viaje_id', $viajeId);
            })
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de asientos obtenido correctamente.', $asientos, fn (AsientoViaje $asiento) => $this->formatAsiento($asiento));
    }

    #[OA\Post(path: "/api/asientosviaje", summary: "Crear un asiento de viaje", security: [["bearerAuth" => []]], tags: ["Asientos de Viaje"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["viaje_id", "numero_asiento"], properties: [
        new OA\Property(property: "viaje_id", type: "integer", example: 1),
        new OA\Property(property: "numero_asiento", type: "string", example: "1A"),
        new OA\Property(property: "estado", type: "string", example: "disponible"),
    ])), responses: [
        new OA\Response(response: 201, description: "Asiento creado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'numero_asiento' => 'required|string',
            'estado' => 'nullable|in:disponible,ocupado,bloqueado',
        ]);

        $data['estado_base'] = 1;
        $data['estado'] = $data['estado'] ?? 'disponible';
        $asiento = AsientoViaje::firstOrCreate(
            [
                'viaje_id' => $data['viaje_id'],
                'numero_asiento' => $data['numero_asiento'],
            ],
            [
                'estado' => $data['estado'],
                'estado_base' => 1,
            ]
        );
        $asiento->load('viaje');

        return $this->successResponse('Asiento creado correctamente.', $this->formatAsiento($asiento), 201);
    }

    #[OA\Get(path: "/api/asientosviaje/{asientoviaje}", summary: "Obtener un asiento de viaje por ID", security: [["bearerAuth" => []]], tags: ["Asientos de Viaje"], parameters: [
        new OA\Parameter(name: "asientoviaje", in: "path", required: true, description: "ID del asiento", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Asiento obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asiento no encontrado"),
    ])]
    public function show(AsientoViaje $asientoviaje): JsonResponse
    {
        $asientoviaje->load('viaje');

        return $this->successResponse('Asiento obtenido correctamente.', $this->formatAsiento($asientoviaje));
    }

    #[OA\Put(path: "/api/asientosviaje/{asientoviaje}", summary: "Actualizar un asiento de viaje", security: [["bearerAuth" => []]], tags: ["Asientos de Viaje"], parameters: [
        new OA\Parameter(name: "asientoviaje", in: "path", required: true, description: "ID del asiento", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["estado"], properties: [
        new OA\Property(property: "estado", type: "string", example: "ocupado"),
    ])), responses: [
        new OA\Response(response: 200, description: "Asiento actualizado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asiento no encontrado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, AsientoViaje $asientoviaje): JsonResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:disponible,ocupado,bloqueado',
        ]);

        $asientoviaje->update($data);

        return $this->successResponse('Asiento actualizado correctamente.', $this->formatAsiento($asientoviaje));
    }

    #[OA\Delete(path: "/api/asientosviaje/{asientoviaje}", summary: "Desactivar un asiento de viaje", security: [["bearerAuth" => []]], tags: ["Asientos de Viaje"], parameters: [
        new OA\Parameter(name: "asientoviaje", in: "path", required: true, description: "ID del asiento", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Asiento desactivado correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Asiento no encontrado"),
    ])]
    public function destroy(AsientoViaje $asientoviaje): JsonResponse
    {
        $asientoviaje->update(['estado_base' => 0]);

        return $this->successResponse('Asiento desactivado correctamente.', $this->formatAsiento($asientoviaje));
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

    private function formatAsiento(AsientoViaje $asiento): array
    {
        return [
            'id' => $asiento->id,
            'viaje_id' => $asiento->viaje_id,
            'numero_asiento' => $asiento->numero_asiento,
            'estado' => $asiento->estado,
            'estado_base' => (bool) $asiento->estado_base,
            'created_at' => $asiento->created_at?->toISOString(),
            'updated_at' => $asiento->updated_at?->toISOString(),
        ];
    }
}
