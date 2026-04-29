<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected readonly UserService $userService
    ) {
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return (new UserResource($user->load(['rol', 'especialidad'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $updated = $this->userService->updateUser($user, $request->validated());
        return new UserResource($updated->load(['rol', 'especialidad']));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->softDelete($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario eliminado (soft delete) y desactivado correctamente.',
        ]);
    }

    public function desactivar(User $user): UserResource
    {
        return new UserResource(
            $this->userService->desactivar($user)->load(['rol', 'especialidad'])
        );
    }

    public function activar(User $user): UserResource
    {
        return new UserResource(
            $this->userService->activar($user)->load(['rol', 'especialidad'])
        );
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['rol.permisos', 'especialidad']));
    }

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            $this->userService->getAllUsers()
        );
    }

    public function medicos(): AnonymousResourceCollection
    {
        return UserResource::collection(
            $this->userService->getMedicos()
        );
    }

    public function roles(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getRoles(),
        ]);
    }

    public function especialidades(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getEspecialidades(),
        ]);
    }
}
