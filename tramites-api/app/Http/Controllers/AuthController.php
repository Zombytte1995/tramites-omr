<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Autentica al usuario y devuelve un token JWT.
     *
     * No se validan las credenciales aquí: auth()->attempt() lo hace
     * internamente contra la tabla users.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = auth('api')->attempt($request->only('email', 'password'));

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
                'errors' => (object) [],
            ], 401);
        }

        return $this->tokenResponse($token);
    }

    /**
     * Invalida el token actual en la lista negra de JWT.
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
            'errors' => (object) [],
        ]);
    }

    /**
     * Emite un nuevo token a partir del token vigente antes de que expire.
     *
     * El token anterior queda invalidado automáticamente.
     */
    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(auth('api')->refresh());
    }

    /**
     * Devuelve los datos del usuario autenticado.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
