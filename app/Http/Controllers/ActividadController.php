<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreActividadRequest;
use App\Http\Requests\UpdateActividadRequest;
use App\Http\Resources\ActividadResource;
use App\Models\Actividad;
use App\Services\ArbolClinicoService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ActividadController extends Controller
{
    public function __construct(
        protected readonly ArbolClinicoService $arbol
    ) {
    }

    public function store(StoreActividadRequest $request): JsonResponse
    {
        $actividad = $this->arbol->crearActividad($request->validated());

        return (new ActividadResource($actividad))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateActividadRequest $request, Actividad $actividad): ActividadResource
    {
        return new ActividadResource(
            $this->arbol->actualizarActividad($actividad, $request->validated())
        );
    }

    public function destroy(Actividad $actividad): JsonResponse
    {
        try {
            $this->arbol->eliminarActividad($actividad);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Actividad eliminada correctamente.',
        ]);
    }
}
