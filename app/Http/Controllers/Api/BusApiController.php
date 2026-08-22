<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Buses", description: "Gestión de buses")]
#[OA\Schema(
    schema: "Bus",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "placa", type: "string", example: "1234-ABC"),
        new OA\Property(property: "capacidad", type: "integer", example: 40),
        new OA\Property(property: "modelo", type: "string", nullable: true, example: "Mercedes Benz O500"),
        new OA\Property(property: "tipo_bus", type: "string", nullable: true, example: "Semicama"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class BusApiController extends Controller
{
    #[OA\Get(
        path: "/api/buses",
        summary: "Listar buses",
        security: [["bearerAuth" => []]],
        tags: ["Buses"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por placa, modelo o tipo de bus", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de buses obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Listado de buses obtenido correctamente."),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Bus")),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 3),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 42),
                            ],
                            type: "object"
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $buses = Bus::query()
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($busQuery) use ($search) {
                    $busQuery->where('placa', 'like', "%{$search}%")
                        ->orWhere('modelo', 'like', "%{$search}%")
                        ->orWhere('tipo_bus', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de buses obtenido correctamente.', $buses, function (Bus $bus) {
            return $this->formatBus($bus);
        });
    }

    #[OA\Post(
        path: "/api/buses",
        summary: "Crear un nuevo bus",
        security: [["bearerAuth" => []]],
        tags: ["Buses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["placa", "capacidad"],
                properties: [
                    new OA\Property(property: "placa", type: "string", example: "1234-ABC"),
                    new OA\Property(property: "capacidad", type: "integer", example: 40),
                    new OA\Property(property: "modelo", type: "string", nullable: true, example: "Mercedes Benz O500"),
                    new OA\Property(property: "tipo_bus", type: "string", nullable: true, example: "Semicama"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Bus creado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bus creado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Bus"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'placa' => 'required|string|max:255|unique:buses,placa',
            'capacidad' => 'required|integer|min:1',
            'modelo' => 'nullable|string|max:255',
            'tipo_bus' => 'nullable|string|max:255',
        ]);

        $bus = Bus::create($data);

        return $this->successResponse('Bus creado correctamente.', $this->formatBus($bus), 201);
    }

    #[OA\Get(
        path: "/api/buses/{bus}",
        summary: "Obtener un bus por ID",
        security: [["bearerAuth" => []]],
        tags: ["Buses"],
        parameters: [
            new OA\Parameter(name: "bus", in: "path", required: true, description: "ID del bus", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bus obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bus obtenido correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Bus"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Bus no encontrado"),
        ]
    )]
    /**
     * Display the specified resource.
     */
    public function show(Bus $bus): JsonResponse
    {
        return $this->successResponse('Bus obtenido correctamente.', $this->formatBus($bus));
    }

    #[OA\Put(
        path: "/api/buses/{bus}",
        summary: "Actualizar un bus",
        security: [["bearerAuth" => []]],
        tags: ["Buses"],
        parameters: [
            new OA\Parameter(name: "bus", in: "path", required: true, description: "ID del bus", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["placa", "capacidad"],
                properties: [
                    new OA\Property(property: "placa", type: "string", example: "1234-ABC"),
                    new OA\Property(property: "capacidad", type: "integer", example: 40),
                    new OA\Property(property: "modelo", type: "string", nullable: true, example: "Mercedes Benz O500"),
                    new OA\Property(property: "tipo_bus", type: "string", nullable: true, example: "Semicama"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Bus actualizado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bus actualizado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Bus"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Bus no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bus $bus): JsonResponse
    {
        $data = $request->validate([
            'placa' => 'required|string|max:255|unique:buses,placa,' . $bus->id,
            'capacidad' => 'required|integer|min:1',
            'modelo' => 'nullable|string|max:255',
            'tipo_bus' => 'nullable|string|max:255',
        ]);

        $bus->update($data);

        return $this->successResponse('Bus actualizado correctamente.', $this->formatBus($bus));
    }

    #[OA\Delete(
        path: "/api/buses/{bus}",
        summary: "Desactivar un bus (soft delete lógico)",
        security: [["bearerAuth" => []]],
        tags: ["Buses"],
        parameters: [
            new OA\Parameter(name: "bus", in: "path", required: true, description: "ID del bus", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bus desactivado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bus desactivado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Bus"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Bus no encontrado"),
        ]
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bus $bus): JsonResponse
    {
        $bus->update(['estado_base' => 0]);

        return $this->successResponse('Bus desactivado correctamente.', $this->formatBus($bus));
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

    private function formatBus(Bus $bus): array
    {
        return [
            'id' => $bus->id,
            'placa' => $bus->placa,
            'capacidad' => $bus->capacidad,
            'modelo' => $bus->modelo,
            'tipo_bus' => $bus->tipo_bus,
            'estado_base' => (bool) $bus->estado_base,
            'created_at' => $bus->created_at?->toISOString(),
            'updated_at' => $bus->updated_at?->toISOString(),
        ];
    }
}
