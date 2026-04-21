<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado y asignado al rol exitosamente.',
            'data' => $user
        ], 201);
    }
    public function Roles(): JsonResponse
    {
        $roles = $this->userService->getRoles();

        return response()->json([
            'status' => 'success',
            'message' => 'Roles obtenidos exitosamente',
            'data' => $roles
        ], 200); // 200 es el código correcto para respuestas GET exitosas
    }
    public function Especialidades(): JsonResponse
    {
        $espcialidades = $this->userService->getEspecialidades();

        return response()->json([
            'status' => 'success',
            'message' => 'Especialidades obtenidos exitosamente',
            'data' => $espcialidades
        ], 200); // 200 es el código correcto para respuestas GET exitosas
    }
}
