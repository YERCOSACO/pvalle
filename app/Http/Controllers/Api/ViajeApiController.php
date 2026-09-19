<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Ruta;
use App\Models\Viaje;
use App\Services\ViajeAsientosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Viajes", description: "Gestión de viajes")]
#[OA\Schema(
    schema: "Viaje",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "ruta_id", type: "integer", example: 1),
        new OA\Property(property: "bus_id", type: "integer", example: 2),
        new OA\Property(property: "fecha_viaje", type: "string", format: "date", example: "2026-09-15"),
        new OA\Property(property: "hora_salida", type: "string", example: "08:00:00"),
        new OA\Property(property: "estado", type: "string", example: "programado"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class ViajeApiController extends Controller
{
    public function __construct(private ViajeAsientosService $asientosService)
    {
    }

    #[OA\Get(path: "/api/viajes", summary: "Listar viajes", security: [["bearerAuth" => []]], tags: ["Viajes"], parameters: [
        new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por origen o destino", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de viajes obtenido correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Listado de viajes obtenido correctamente."),
            new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Viaje")),
            new OA\Property(property: "meta", properties: [
                new OA\Property(property: "current_page", type: "integer", example: 1),
                new OA\Property(property: "last_page", type: "integer", example: 2),
                new OA\Property(property: "per_page", type: "integer", example: 15),
                new OA\Property(property: "total", type: "integer", example: 9),
            ], type: "object"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $viajes = Viaje::query()
            ->with(['ruta', 'bus'])
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->whereHas('ruta', function ($rutaQuery) use ($search) {
                    $rutaQuery->where('origen', 'like', "%{$search}%")
                        ->orWhere('destino', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de viajes obtenido correctamente.', $viajes, fn (Viaje $viaje) => $this->formatViaje($viaje));
    }

    #[OA\Post(path: "/api/viajes", summary: "Crear un viaje", security: [["bearerAuth" => []]], tags: ["Viajes"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["ruta_id", "bus_id", "fecha_viaje", "hora_salida"], properties: [
        new OA\Property(property: "ruta_id", type: "integer", example: 1),
        new OA\Property(property: "bus_id", type: "integer", example: 2),
        new OA\Property(property: "fecha_viaje", type: "string", format: "date", example: "2026-09-15"),
        new OA\Property(property: "hora_salida", type: "string", example: "08:00:00"),
        new OA\Property(property: "estado", type: "string", example: "programado"),
    ])), responses: [
        new OA\Response(response: 201, description: "Viaje creado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Viaje creado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Viaje"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'bus_id' => 'required|exists:buses,id',
            'fecha_viaje' => 'required|date',
            'hora_salida' => 'required',
            'estado' => 'nullable|in:programado,en curso,finalizado,cancelado',
        ]);

        $viaje = DB::transaction(function () use ($data, $request) {
            $viaje = Viaje::create($data);
            $bus = Bus::findOrFail($request->bus_id);
            $this->asientosService->crearParaViaje($viaje);

            return $viaje;
        });

        return $this->successResponse('Viaje creado correctamente.', $this->formatViaje($viaje), 201);
    }

    #[OA\Get(path: "/api/viajes/{viaje}", summary: "Obtener un viaje por ID", security: [["bearerAuth" => []]], tags: ["Viajes"], parameters: [
        new OA\Parameter(name: "viaje", in: "path", required: true, description: "ID del viaje", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Viaje obtenido correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Viaje obtenido correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Viaje"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Viaje no encontrado"),
    ])]
    public function show(Viaje $viaje): JsonResponse
    {
        $viaje->load(['ruta', 'bus']);

        return $this->successResponse('Viaje obtenido correctamente.', $this->formatViaje($viaje));
    }

    #[OA\Put(path: "/api/viajes/{viaje}", summary: "Actualizar un viaje", security: [["bearerAuth" => []]], tags: ["Viajes"], parameters: [
        new OA\Parameter(name: "viaje", in: "path", required: true, description: "ID del viaje", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["ruta_id", "bus_id", "fecha_viaje", "hora_salida"], properties: [
        new OA\Property(property: "ruta_id", type: "integer", example: 1),
        new OA\Property(property: "bus_id", type: "integer", example: 2),
        new OA\Property(property: "fecha_viaje", type: "string", format: "date", example: "2026-09-15"),
        new OA\Property(property: "hora_salida", type: "string", example: "08:00:00"),
        new OA\Property(property: "estado", type: "string", example: "programado"),
    ])), responses: [
        new OA\Response(response: 200, description: "Viaje actualizado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Viaje actualizado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Viaje"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Viaje no encontrado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, Viaje $viaje): JsonResponse
    {
        $data = $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'bus_id' => 'required|exists:buses,id',
            'fecha_viaje' => 'required|date',
            'hora_salida' => 'required',
            'estado' => 'required|in:programado,en curso,finalizado,cancelado',
        ]);

        $viaje->update($data);

        return $this->successResponse('Viaje actualizado correctamente.', $this->formatViaje($viaje));
    }

    #[OA\Delete(path: "/api/viajes/{viaje}", summary: "Desactivar un viaje", security: [["bearerAuth" => []]], tags: ["Viajes"], parameters: [
        new OA\Parameter(name: "viaje", in: "path", required: true, description: "ID del viaje", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Viaje desactivado correctamente", content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Viaje desactivado correctamente."),
            new OA\Property(property: "data", ref: "#/components/schemas/Viaje"),
        ])),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Viaje no encontrado"),
    ])]
    public function destroy(Viaje $viaje): JsonResponse
    {
        $viaje->update(['estado_base' => 0]);

        return $this->successResponse('Viaje desactivado correctamente.', $this->formatViaje($viaje));
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

    private function formatViaje(Viaje $viaje): array
    {
        return [
            'id' => $viaje->id,
            'ruta_id' => $viaje->ruta_id,
            'bus_id' => $viaje->bus_id,
            'fecha_viaje' => $viaje->fecha_viaje ? \Carbon\Carbon::parse($viaje->fecha_viaje)->format('Y-m-d') : null,
            'hora_salida' => $viaje->hora_salida,
            'estado' => $viaje->estado,
            'ruta' => $viaje->ruta ? [
                'id' => $viaje->ruta->id,
                'nombre_ruta' => $viaje->ruta->nombre_ruta,
                'origen' => $viaje->ruta->origen,
                'destino' => $viaje->ruta->destino,
            ] : null,
            'bus' => $viaje->bus ? [
                'id' => $viaje->bus->id,
                'placa' => $viaje->bus->placa,
                'capacidad' => $viaje->bus->capacidad,
            ] : null,
            'estado_base' => (bool) $viaje->estado_base,
            'created_at' => $viaje->created_at?->toISOString(),
            'updated_at' => $viaje->updated_at?->toISOString(),
        ];
    }
}
