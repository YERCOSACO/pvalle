<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Rutas", description: "Gestión de rutas")]
#[OA\Schema(
    schema: "Ruta",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "origen", type: "string", example: "La Paz"),
        new OA\Property(property: "destino", type: "string", example: "Santa Cruz"),
        new OA\Property(property: "distancia_km", type: "number", format: "float", nullable: true, example: 540.5),
        new OA\Property(property: "precio_base", type: "number", format: "float", example: 120.00),
        new OA\Property(property: "nombre_ruta", type: "string", example: "La Paz → Santa Cruz"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class RutaApiController extends Controller
{
    #[OA\Get(
        path: "/api/rutas",
        summary: "Listar rutas",
        security: [["bearerAuth" => []]],
        tags: ["Rutas"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por origen o destino", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de rutas obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Listado de rutas obtenido correctamente."),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Ruta")),
                        new OA\Property(property: "meta", properties: [
                            new OA\Property(property: "current_page", type: "integer", example: 1),
                            new OA\Property(property: "last_page", type: "integer", example: 2),
                            new OA\Property(property: "per_page", type: "integer", example: 15),
                            new OA\Property(property: "total", type: "integer", example: 18),
                        ], type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $rutas = Ruta::query()
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($rutaQuery) use ($search) {
                    $rutaQuery->where('origen', 'like', "%{$search}%")
                        ->orWhere('destino', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de rutas obtenido correctamente.', $rutas, fn (Ruta $ruta) => $this->formatRuta($ruta));
    }

    #[OA\Post(
        path: "/api/rutas",
        summary: "Crear una ruta",
        security: [["bearerAuth" => []]],
        tags: ["Rutas"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["origen", "destino", "precio_base"],
                properties: [
                    new OA\Property(property: "origen", type: "string", example: "La Paz"),
                    new OA\Property(property: "destino", type: "string", example: "Santa Cruz"),
                    new OA\Property(property: "distancia_km", type: "number", format: "float", nullable: true, example: 540.5),
                    new OA\Property(property: "precio_base", type: "number", format: "float", example: 120.00),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Ruta creada correctamente", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Ruta creada correctamente."),
                new OA\Property(property: "data", ref: "#/components/schemas/Ruta"),
            ])),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $ruta = Ruta::create($data);

        return $this->successResponse('Ruta creada correctamente.', $this->formatRuta($ruta), 201);
    }

    #[OA\Get(
        path: "/api/rutas/{ruta}",
        summary: "Obtener una ruta por ID",
        security: [["bearerAuth" => []]],
        tags: ["Rutas"],
        parameters: [
            new OA\Parameter(name: "ruta", in: "path", required: true, description: "ID de la ruta", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Ruta obtenida correctamente", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Ruta obtenida correctamente."),
                new OA\Property(property: "data", ref: "#/components/schemas/Ruta"),
            ])),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Ruta no encontrada"),
        ]
    )]
    public function show(Ruta $ruta): JsonResponse
    {
        return $this->successResponse('Ruta obtenida correctamente.', $this->formatRuta($ruta));
    }

    #[OA\Put(
        path: "/api/rutas/{ruta}",
        summary: "Actualizar una ruta",
        security: [["bearerAuth" => []]],
        tags: ["Rutas"],
        parameters: [
            new OA\Parameter(name: "ruta", in: "path", required: true, description: "ID de la ruta", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["origen", "destino", "precio_base"],
                properties: [
                    new OA\Property(property: "origen", type: "string", example: "La Paz"),
                    new OA\Property(property: "destino", type: "string", example: "Santa Cruz"),
                    new OA\Property(property: "distancia_km", type: "number", format: "float", nullable: true, example: 540.5),
                    new OA\Property(property: "precio_base", type: "number", format: "float", example: 120.00),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Ruta actualizada correctamente", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Ruta actualizada correctamente."),
                new OA\Property(property: "data", ref: "#/components/schemas/Ruta"),
            ])),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Ruta no encontrada"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function update(Request $request, Ruta $ruta): JsonResponse
    {
        $data = $request->validate([
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $ruta->update($data);

        return $this->successResponse('Ruta actualizada correctamente.', $this->formatRuta($ruta));
    }

    #[OA\Delete(
        path: "/api/rutas/{ruta}",
        summary: "Desactivar una ruta",
        security: [["bearerAuth" => []]],
        tags: ["Rutas"],
        parameters: [
            new OA\Parameter(name: "ruta", in: "path", required: true, description: "ID de la ruta", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Ruta desactivada correctamente", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Ruta desactivada correctamente."),
                new OA\Property(property: "data", ref: "#/components/schemas/Ruta"),
            ])),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Ruta no encontrada"),
        ]
    )]
    public function destroy(Ruta $ruta): JsonResponse
    {
        $ruta->update(['estado_base' => 0]);

        return $this->successResponse('Ruta desactivada correctamente.', $this->formatRuta($ruta));
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

    private function formatRuta(Ruta $ruta): array
    {
        return [
            'id' => $ruta->id,
            'origen' => $ruta->origen,
            'destino' => $ruta->destino,
            'distancia_km' => $ruta->distancia_km,
            'precio_base' => $ruta->precio_base,
            'nombre_ruta' => $ruta->nombre_ruta,
            'estado_base' => (bool) $ruta->estado_base,
            'created_at' => $ruta->created_at?->toISOString(),
            'updated_at' => $ruta->updated_at?->toISOString(),
        ];
    }
}
