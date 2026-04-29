<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRespuestaRequest;
use App\Http\Requests\UpdateRespuestaRequest;
use App\Http\Resources\RespuestaResource;
use App\Models\Respuesta;
use App\Services\ArbolClinicoService;
use Illuminate\Http\JsonResponse;

class RespuestaController extends Controller
{
    public function __construct(
        protected readonly ArbolClinicoService $arbol
    ) {
    }

    public function store(StoreRespuestaRequest $request): JsonResponse
    {
        $respuesta = $this->arbol->crearRespuesta($request->validated());

        return (new RespuestaResource($respuesta))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateRespuestaRequest $request, Respuesta $respuesta): RespuestaResource
    {
        return new RespuestaResource(
            $this->arbol->actualizarRespuesta($respuesta, $request->validated())
        );
    }

    public function destroy(Respuesta $respuesta): JsonResponse
    {
        $this->arbol->eliminarRespuesta($respuesta);

        return response()->json([
            'status' => 'success',
            'message' => 'Respuesta eliminada correctamente.',
        ]);
    }
}
