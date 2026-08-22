<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Clientes", description: "Gestión de clientes")]
#[OA\Schema(
    schema: "Cliente",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "María"),
        new OA\Property(property: "apellido", type: "string", example: "Gómez"),
        new OA\Property(property: "nombre_completo", type: "string", example: "María Gómez"),
        new OA\Property(property: "cedula", type: "string", example: "9876543"),
        new OA\Property(property: "email", type: "string", format: "email", example: "maria@correo.com"),
        new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
        new OA\Property(property: "direccion", type: "string", nullable: true, example: "Av. Siempre Viva 123"),
        new OA\Property(property: "fecha_nacimiento", type: "string", format: "date", nullable: true, example: "1995-05-20"),
        new OA\Property(property: "estado_base", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class ClienteApiController extends Controller
{
    #[OA\Get(
        path: "/api/clientes",
        summary: "Listar clientes",
        security: [["bearerAuth" => []]],
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false, description: "Buscar por nombre, apellido, cédula o email", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", required: false, description: "Número de página", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de clientes obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Listado de clientes obtenido correctamente."),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Cliente")),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 2),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 25),
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
        $clientes = Cliente::query()
            ->activos()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('cedula', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->paginatedResponse('Listado de clientes obtenido correctamente.', $clientes, function (Cliente $cliente) {
            return $this->formatCliente($cliente);
        });
    }

    #[OA\Post(
        path: "/api/clientes",
        summary: "Crear un nuevo cliente",
        security: [["bearerAuth" => []]],
        tags: ["Clientes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "cedula", "email", "contrasena", "contrasena_confirmation"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "María"),
                    new OA\Property(property: "apellido", type: "string", example: "Gómez"),
                    new OA\Property(property: "cedula", type: "string", example: "9876543"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "maria@correo.com"),
                    new OA\Property(property: "contrasena", type: "string", format: "password", example: "secreto123"),
                    new OA\Property(property: "contrasena_confirmation", type: "string", format: "password", example: "secreto123"),
                    new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
                    new OA\Property(property: "direccion", type: "string", nullable: true, example: "Av. Siempre Viva 123"),
                    new OA\Property(property: "fecha_nacimiento", type: "string", format: "date", nullable: true, example: "1995-05-20"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Cliente creado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Cliente creado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Cliente"),
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
            'cedula' => 'required|string|max:255|unique:clientes,cedula',
            'email' => 'required|email|max:255|unique:clientes,email',
            'contrasena' => 'required|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $data['contrasena'] = Hash::make($data['contrasena']);
        $cliente = Cliente::create($data);

        return $this->successResponse('Cliente creado correctamente.', $this->formatCliente($cliente), 201);
    }

    #[OA\Get(
        path: "/api/clientes/{cliente}",
        summary: "Obtener un cliente por ID",
        security: [["bearerAuth" => []]],
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "cliente", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cliente obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Cliente obtenido correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Cliente"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Cliente no encontrado"),
        ]
    )]
    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente): JsonResponse
    {
        return $this->successResponse('Cliente obtenido correctamente.', $this->formatCliente($cliente));
    }

    #[OA\Put(
        path: "/api/clientes/{cliente}",
        summary: "Actualizar un cliente",
        security: [["bearerAuth" => []]],
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "cliente", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "cedula", "email"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "María"),
                    new OA\Property(property: "apellido", type: "string", example: "Gómez"),
                    new OA\Property(property: "cedula", type: "string", example: "9876543"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "maria@correo.com"),
                    new OA\Property(property: "contrasena", type: "string", format: "password", nullable: true, description: "Opcional, dejar vacío para no cambiarla", example: "nuevoSecreto123"),
                    new OA\Property(property: "contrasena_confirmation", type: "string", format: "password", nullable: true, example: "nuevoSecreto123"),
                    new OA\Property(property: "telefono", type: "string", nullable: true, example: "70011122"),
                    new OA\Property(property: "direccion", type: "string", nullable: true, example: "Av. Siempre Viva 123"),
                    new OA\Property(property: "fecha_nacimiento", type: "string", format: "date", nullable: true, example: "1995-05-20"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Cliente actualizado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Cliente actualizado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Cliente"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Cliente no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cedula' => 'required|string|max:255|unique:clientes,cedula,' . $cliente->id,
            'email' => 'required|email|max:255|unique:clientes,email,' . $cliente->id,
            'contrasena' => 'nullable|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        if (! empty($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        } else {
            unset($data['contrasena']);
        }

        $cliente->update($data);

        return $this->successResponse('Cliente actualizado correctamente.', $this->formatCliente($cliente));
    }

    #[OA\Delete(
        path: "/api/clientes/{cliente}",
        summary: "Desactivar un cliente (soft delete lógico)",
        security: [["bearerAuth" => []]],
        tags: ["Clientes"],
        parameters: [
            new OA\Parameter(name: "cliente", in: "path", required: true, description: "ID del cliente", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cliente desactivado correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Cliente desactivado correctamente."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Cliente"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 404, description: "Cliente no encontrado"),
        ]
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->update(['estado_base' => 0]);

        return $this->successResponse('Cliente desactivado correctamente.', $this->formatCliente($cliente));
    }

    private function paginatedResponse(string $message, LengthAwarePaginator $paginator, callable $formatter): JsonResponse
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

    private function formatCliente(Cliente $cliente): array
    {
        return [
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
            'apellido' => $cliente->apellido,
            'nombre_completo' => $cliente->nombre_completo,
            'cedula' => $cliente->cedula,
            'email' => $cliente->email,
            'telefono' => $cliente->telefono,
            'direccion' => $cliente->direccion,
            'fecha_nacimiento' => $cliente->fecha_nacimiento instanceof \DateTime ? $cliente->fecha_nacimiento->format('Y-m-d') : $cliente->fecha_nacimiento,            
            'estado_base' => (bool) $cliente->estado_base,
            'created_at' => $cliente->created_at?->toISOString(),
            'updated_at' => $cliente->updated_at?->toISOString(),
        ];
    }
}
