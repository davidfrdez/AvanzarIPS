<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreObjetivoRequest;
use App\Http\Requests\UpdateObjetivoRequest;
use App\Http\Resources\ObjetivoResource;
use App\Models\Objetivo;
use App\Services\ArbolClinicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

class ObjetivoController extends Controller
{
    public function __construct(
        protected readonly ArbolClinicoService $arbol
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return ObjetivoResource::collection(
            $this->arbol->arbolCompleto()
        );
    }

    public function show(Objetivo $objetivo): ObjetivoResource
    {
        return new ObjetivoResource($objetivo->load('actividades.respuestas'));
    }

    public function store(StoreObjetivoRequest $request): JsonResponse
    {
        $objetivo = $this->arbol->crearObjetivo($request->validated());

        return (new ObjetivoResource($objetivo))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateObjetivoRequest $request, Objetivo $objetivo): ObjetivoResource
    {
        return new ObjetivoResource(
            $this->arbol->actualizarObjetivo($objetivo, $request->validated())
        );
    }

    public function destroy(Objetivo $objetivo): JsonResponse
    {
        try {
            $this->arbol->eliminarObjetivo($objetivo);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Objetivo eliminado correctamente.',
        ]);
    }
}
