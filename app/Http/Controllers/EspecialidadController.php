<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use App\Models\Especialidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\EspecialidadResource;

class EspecialidadController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return EspecialidadResource::collection(
            Especialidad::query()->orderBy('nombre')->get()
        );
    }

    public function show(Especialidad $especialidad): EspecialidadResource
    {
        return new EspecialidadResource($especialidad);
    }

    public function store(StoreEspecialidadRequest $request): JsonResponse
    {
        $especialidad = Especialidad::create($request->validated());

        return (new EspecialidadResource($especialidad))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEspecialidadRequest $request, Especialidad $especialidad): EspecialidadResource
    {
        $especialidad->update($request->validated());
        return new EspecialidadResource($especialidad->refresh());
    }

    public function destroy(Especialidad $especialidad): JsonResponse
    {
        $user = request()->user();
        if ($user === null || !$user->tienePermiso('especialidades.gestionar')) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        if ($especialidad->medicos()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar la especialidad: tiene profesionales asignados.',
            ], 409);
        }

        if ($especialidad->citas()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar la especialidad: tiene citas registradas.',
            ], 409);
        }

        $especialidad->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Especialidad eliminada correctamente.',
        ]);
    }
}
