<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Notificaciones", description: "Gestión de notificaciones")]
#[OA\Schema(
    schema: "Notificacion",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "cliente_id", type: "integer", example: 1),
        new OA\Property(property: "titulo", type: "string", example: "Tu boleto ha sido confirmado"),
        new OA\Property(property: "mensaje", type: "string", example: "Tu boleto para el viaje del 15/09/2026 ha sido confirmado."),
        new OA\Property(property: "tipo", type: "string", example: "info"),
        new OA\Property(property: "canal", type: "string", example: "sistema"),
        new OA\Property(property: "prioridad", type: "string", example: "normal"),
        new OA\Property(property: "estado", type: "string", example: "pendiente"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class NotificacionApiController extends Controller
{
    #[OA\Get(path: "/api/notificaciones", summary: "Listar notificaciones", security: [["bearerAuth" => []]], tags: ["Notificaciones"], parameters: [
        new OA\Parameter(name: "estado", in: "query", required: false, description: "Filtrar por estado", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Listado de notificaciones obtenido correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
    ])]
    public function index(Request $request): JsonResponse
    {
        $notificaciones = Notificacion::query()
            ->with('cliente')
            ->where('estado_base', 1)
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de notificaciones obtenido correctamente.', $notificaciones, fn (Notificacion $notificacion) => $this->formatNotificacion($notificacion));
    }

    #[OA\Post(path: "/api/notificaciones", summary: "Crear una notificación", security: [["bearerAuth" => []]], tags: ["Notificaciones"], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["cliente_id", "titulo", "mensaje"], properties: [
        new OA\Property(property: "cliente_id", type: "integer", example: 1),
        new OA\Property(property: "titulo", type: "string", example: "Tu boleto ha sido confirmado"),
        new OA\Property(property: "mensaje", type: "string", example: "Tu boleto para el viaje del 15/09/2026 ha sido confirmado."),
        new OA\Property(property: "tipo", type: "string", example: "info"),
        new OA\Property(property: "canal", type: "string", example: "sistema"),
        new OA\Property(property: "prioridad", type: "string", example: "normal"),
    ])), responses: [
        new OA\Response(response: 201, description: "Notificación creada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|in:info,alerta,urgente',
            'canal' => 'nullable|in:sistema,email,sms',
            'prioridad' => 'nullable|in:baja,normal,alta',
            'estado' => 'nullable|in:pendiente,enviada,leida',
        ]);

        $data['estado_base'] = 1;
        $data['tipo'] = $data['tipo'] ?? 'info';
        $data['canal'] = $data['canal'] ?? 'sistema';
        $data['prioridad'] = $data['prioridad'] ?? 'normal';
        $data['estado'] = $data['estado'] ?? 'pendiente';
        $notificacion = Notificacion::create($data);
        $notificacion->load('cliente');

        return $this->successResponse('Notificación creada correctamente.', $this->formatNotificacion($notificacion), 201);
    }

    #[OA\Get(path: "/api/notificaciones/{notificacion}", summary: "Obtener una notificación por ID", security: [["bearerAuth" => []]], tags: ["Notificaciones"], parameters: [
        new OA\Parameter(name: "notificacion", in: "path", required: true, description: "ID de la notificación", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Notificación obtenida correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Notificación no encontrada"),
    ])]
    public function show(Notificacion $notificacion): JsonResponse
    {
        $notificacion->load('cliente');

        return $this->successResponse('Notificación obtenida correctamente.', $this->formatNotificacion($notificacion));
    }

    #[OA\Put(path: "/api/notificaciones/{notificacion}", summary: "Actualizar una notificación", security: [["bearerAuth" => []]], tags: ["Notificaciones"], parameters: [
        new OA\Parameter(name: "notificacion", in: "path", required: true, description: "ID de la notificación", schema: new OA\Schema(type: "integer")),
    ], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ["titulo", "mensaje"], properties: [
        new OA\Property(property: "titulo", type: "string", example: "Tu boleto ha sido confirmado"),
        new OA\Property(property: "mensaje", type: "string", example: "Tu boleto para el viaje del 15/09/2026 ha sido confirmado."),
        new OA\Property(property: "tipo", type: "string", example: "info"),
        new OA\Property(property: "canal", type: "string", example: "sistema"),
        new OA\Property(property: "prioridad", type: "string", example: "normal"),
        new OA\Property(property: "estado", type: "string", example: "leida"),
    ])), responses: [
        new OA\Response(response: 200, description: "Notificación actualizada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Notificación no encontrada"),
        new OA\Response(response: 422, description: "Error de validación"),
    ])]
    public function update(Request $request, Notificacion $notificacion): JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|in:info,alerta,urgente',
            'canal' => 'nullable|in:sistema,email,sms',
            'prioridad' => 'nullable|in:baja,normal,alta',
            'estado' => 'nullable|in:pendiente,enviada,leida',
        ]);

        $notificacion->update($data);

        return $this->successResponse('Notificación actualizada correctamente.', $this->formatNotificacion($notificacion));
    }

    #[OA\Delete(path: "/api/notificaciones/{notificacion}", summary: "Desactivar una notificación", security: [["bearerAuth" => []]], tags: ["Notificaciones"], parameters: [
        new OA\Parameter(name: "notificacion", in: "path", required: true, description: "ID de la notificación", schema: new OA\Schema(type: "integer")),
    ], responses: [
        new OA\Response(response: 200, description: "Notificación desactivada correctamente"),
        new OA\Response(response: 401, description: "No autenticado"),
        new OA\Response(response: 404, description: "Notificación no encontrada"),
    ])]
    public function destroy(Notificacion $notificacion): JsonResponse
    {
        $notificacion->forceFill(['estado_base' => 0])->save();

        return $this->successResponse('Notificación desactivada correctamente.', $this->formatNotificacion($notificacion));
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

    private function formatNotificacion(Notificacion $notificacion): array
    {
        return [
            'id' => $notificacion->id,
            'cliente_id' => $notificacion->cliente_id,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'tipo' => $notificacion->tipo,
            'canal' => $notificacion->canal,
            'prioridad' => $notificacion->prioridad,
            'estado' => $notificacion->estado,
            'fecha_envio' => $notificacion->fecha_envio?->toISOString(),
            'fecha_lectura' => $notificacion->fecha_lectura?->toISOString(),
            'estado_base' => (bool) $notificacion->estado_base,
            'created_at' => $notificacion->created_at?->toISOString(),
            'updated_at' => $notificacion->updated_at?->toISOString(),
        ];
    }
}
