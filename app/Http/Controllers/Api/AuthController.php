<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource; // Importación corregida
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Ingreso exitoso',
            'data' => [
                'token' => $result['token'],
                'user'  => new UserResource($result['user'])
            ]
        ]);
    }
    public function logout(Request $request): JsonResponse
    {
        // El $request->user() nos da el usuario autenticado gracias al middleware sanctum
        $this->authService->logout($request->user());

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}