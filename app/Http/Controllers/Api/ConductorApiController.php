<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Conductores", description: "Gestión de conductores")]
#[OA\Schema(
    schema: "Conductor",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Carlos"),
        new OA\Property(property: "apellido", type: "string", example: "Rojas"),
        new OA\Property(property: "nombre_completo", type: "string", example: "Carlos Rojas"),
        new OA\Property(property: "licencia", type: "string", example: "LIC-00123"),
        new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class ConductorApiController extends Controller
{
    #[OA\Get(
        path: "/api/conductores",
        summary: "Listar conductores",
        security: [["bearerAuth" => []]],
        tags: ["Conductores"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por nombre, apellido o licencia", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de conductores obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Listado de conductores obtenido correctamente."),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Conductor")),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 2),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 18),
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
        $conductores = Conductor::query()
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($conductorQuery) use ($search) {
                    $conductorQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('licencia', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de conductores obtenido correctamente.', $conductores, function (Conductor $conductor) {
            return $this->formatConductor($conductor);
        });
    }

    #[OA\Post(
        path: "/api/conductores",
        summary: "Crear un nuevo conductor",
        security: [["bearerAuth" => []]],
        tags: ["Conductores"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "licencia"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Carlos"),
                    new OA\Property(property: "apellido", type: "string", example: "Rojas"),
                    new OA\Property(property: "licencia", type: "string", example: "LIC-00123"),
                    new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Conductor creado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Conductor creado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Conductor"),
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
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'licencia' => 'required|string|max:255|unique:conductores,licencia',
            'telefono' => 'nullable|string|max:20',
        ]);

        $conductor = Conductor::create($data);

        return $this->successResponse('Conductor creado correctamente.', $this->formatConductor($conductor), 201);
    }

    #[OA\Get(
        path: "/api/conductores/{conductor}",
        summary: "Obtener un conductor por ID",
        security: [["bearerAuth" => []]],
        tags: ["Conductores"],
        parameters: [
            new OA\Parameter(name: "conductor", in: "path", required: true, description: "ID del conductor", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Conductor obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Conductor obtenido correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Conductor"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Conductor no encontrado"),
        ]
    )]
    /**
     * Display the specified resource.
     */
    public function show(Conductor $conductor): JsonResponse
    {
        return $this->successResponse('Conductor obtenido correctamente.', $this->formatConductor($conductor));
    }

    #[OA\Put(
        path: "/api/conductores/{conductor}",
        summary: "Actualizar un conductor",
        security: [["bearerAuth" => []]],
        tags: ["Conductores"],
        parameters: [
            new OA\Parameter(name: "conductor", in: "path", required: true, description: "ID del conductor", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "licencia"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Carlos"),
                    new OA\Property(property: "apellido", type: "string", example: "Rojas"),
                    new OA\Property(property: "licencia", type: "string", example: "LIC-00123"),
                    new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Conductor actualizado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Conductor actualizado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Conductor"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Conductor no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conductor $conductor): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'licencia' => 'required|string|max:255|unique:conductores,licencia,' . $conductor->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $conductor->update($data);

        return $this->successResponse('Conductor actualizado correctamente.', $this->formatConductor($conductor));
    }

    #[OA\Delete(
        path: "/api/conductores/{conductor}",
        summary: "Desactivar un conductor (soft delete lógico)",
        security: [["bearerAuth" => []]],
        tags: ["Conductores"],
        parameters: [
            new OA\Parameter(name: "conductor", in: "path", required: true, description: "ID del conductor", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Conductor desactivado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Conductor desactivado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Conductor"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Conductor no encontrado"),
        ]
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conductor $conductor): JsonResponse
    {
        $conductor->update(['estado_base' => 0]);

        return $this->successResponse('Conductor desactivado correctamente.', $this->formatConductor($conductor));
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

    private function formatConductor(Conductor $conductor): array
    {
        return [
            'id' => $conductor->id,
            'nombre' => $conductor->nombre,
            'apellido' => $conductor->apellido,
            'nombre_completo' => $conductor->nombre_completo,
            'licencia' => $conductor->licencia,
            'telefono' => $conductor->telefono,
            'estado_base' => (bool) $conductor->estado_base,
            'created_at' => $conductor->created_at?->toISOString(),
            'updated_at' => $conductor->updated_at?->toISOString(),
        ];
    }
}
