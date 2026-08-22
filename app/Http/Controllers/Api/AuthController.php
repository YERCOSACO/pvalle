<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Autenticación de usuarios")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/login",
        summary: "Iniciar sesión",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "usuario@correo.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secreto123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Login exitoso."),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                        new OA\Property(property: "email", type: "string", example: "usuario@correo.com"),
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
                                new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600),
                            ],
                            type: "object"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Credenciales incorrectas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Credenciales incorrectas."),
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    /**
     * Login de usuario
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return $this->errorResponse('Credenciales incorrectas.', 401);
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();

        return $this->successResponse('Login exitoso.', [
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Cerrar sesión",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sesión cerrada correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Sesión cerrada correctamente."),
                        new OA\Property(property: "data", type: "array", items: new OA\Items()),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    /**
     * Logout de usuario
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return $this->successResponse('Sesión cerrada correctamente.');
    }

    #[OA\Get(
        path: "/api/me",
        summary: "Obtener usuario autenticado (alias: /api/perfil)",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Perfil obtenido correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Perfil obtenido correctamente."),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                        new OA\Property(property: "email", type: "string", example: "usuario@correo.com"),
                                    ],
                                    type: "object"
                                ),
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
     * Obtener usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user('api') ?? Auth::guard('api')->user();

        return $this->successResponse('Perfil obtenido correctamente.', [
            'user' => $this->formatUser($user),
        ]);
    }

    private function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private function formatUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
