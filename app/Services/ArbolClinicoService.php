<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Actividad;
use App\Models\Objetivo;
use App\Models\Respuesta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Servicio para el árbol clínico estricto:
 *   Objetivo (raíz) → Actividad (rama) → Respuesta (hoja)
 */
final class ArbolClinicoService
{
    /** @return Collection<int, Objetivo> */
    public function arbolCompleto(): Collection
    {
        return Objetivo::with('actividades.respuestas')->orderBy('nombre')->get();
    }

    // ----- OBJETIVO -----

    /** @param array<string, mixed> $data */
    public function crearObjetivo(array $data): Objetivo
    {
        return DB::transaction(static fn (): Objetivo => Objetivo::create([
            'nombre' => (string) $data['nombre'],
            'descripcion' => isset($data['descripcion']) ? (string) $data['descripcion'] : null,
        ]));
    }

    /** @param array<string, mixed> $data */
    public function actualizarObjetivo(Objetivo $objetivo, array $data): Objetivo
    {
        return DB::transaction(function () use ($objetivo, $data): Objetivo {
            $objetivo->update(array_filter([
                'nombre' => $data['nombre'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
            ], static fn ($v): bool => $v !== null));
            return $objetivo->refresh();
        });
    }

    /** @throws RuntimeException si tiene actividades */
    public function eliminarObjetivo(Objetivo $objetivo): bool
    {
        if ($objetivo->actividades()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar el objetivo: tiene actividades asociadas. Elimine primero las actividades.'
            );
        }
        return (bool) $objetivo->delete();
    }

    // ----- ACTIVIDAD -----

    /**
     * @param  array<string, mixed>  $data
     * @throws Throwable
     */
    public function crearActividad(array $data): Actividad
    {
        return DB::transaction(static fn (): Actividad => Actividad::create([
            'objetivo_id' => (int) $data['objetivo_id'],
            'nombre' => (string) $data['nombre'],
        ]));
    }

    /** @param array<string, mixed> $data */
    public function actualizarActividad(Actividad $actividad, array $data): Actividad
    {
        return DB::transaction(function () use ($actividad, $data): Actividad {
            $actividad->update(array_filter([
                'objetivo_id' => isset($data['objetivo_id']) ? (int) $data['objetivo_id'] : null,
                'nombre' => $data['nombre'] ?? null,
            ], static fn ($v): bool => $v !== null));
            return $actividad->refresh();
        });
    }

    /** @throws RuntimeException si tiene respuestas */
    public function eliminarActividad(Actividad $actividad): bool
    {
        if ($actividad->respuestas()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar la actividad: tiene respuestas predeterminadas asociadas.'
            );
        }
        return (bool) $actividad->delete();
    }

    // ----- RESPUESTA -----

    /** @param array<string, mixed> $data */
    public function crearRespuesta(array $data): Respuesta
    {
        return DB::transaction(static fn (): Respuesta => Respuesta::create([
            'actividad_id' => (int) $data['actividad_id'],
            'texto_predeterminado' => (string) $data['texto_predeterminado'],
        ]));
    }

    /** @param array<string, mixed> $data */
    public function actualizarRespuesta(Respuesta $respuesta, array $data): Respuesta
    {
        return DB::transaction(function () use ($respuesta, $data): Respuesta {
            $respuesta->update(array_filter([
                'actividad_id' => isset($data['actividad_id']) ? (int) $data['actividad_id'] : null,
                'texto_predeterminado' => $data['texto_predeterminado'] ?? null,
            ], static fn ($v): bool => $v !== null));
            return $respuesta->refresh();
        });
    }

    public function eliminarRespuesta(Respuesta $respuesta): bool
    {
        return (bool) $respuesta->delete();
    }
}
