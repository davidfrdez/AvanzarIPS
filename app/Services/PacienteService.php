<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\HistoriaClinicaIngresoDTO;
use App\DTOs\PacienteDTO;
use App\Models\HistoriaClinicaIngreso;
use App\Models\Paciente;
use App\Services\Contracts\PacienteServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PacienteService implements PacienteServiceInterface
{
    /**
     * Crea Paciente + HistoriaClinicaIngreso opcional en una transacción atómica.
     * La auditoría se delega al Trait Auditable (boot hooks).
     *
     * @throws Throwable
     */
    public function create(PacienteDTO $paciente, ?HistoriaClinicaIngresoDTO $ingreso = null): Paciente
    {
        return DB::transaction(function () use ($paciente, $ingreso): Paciente {
            /** @var Paciente $model */
            $model = Paciente::create($paciente->toModelArray());

            if ($ingreso instanceof HistoriaClinicaIngresoDTO) {
                HistoriaClinicaIngreso::create($ingreso->toModelArray($model->id));
            }

            return $model->fresh(['historiasClinicasIngreso']) ?? $model;
        });
    }

    /** @throws Throwable */
    public function update(Paciente $paciente, PacienteDTO $dto): Paciente
    {
        return DB::transaction(function () use ($paciente, $dto): Paciente {
            $paciente->update($dto->toModelArray());

            return $paciente->refresh();
        });
    }

    public function softDelete(Paciente $paciente): bool
    {
        return (bool) $paciente->delete();
    }

    public function darAlta(Paciente $paciente): Paciente
    {
        $paciente->update(['esta_activo' => false]);
        return $paciente->refresh();
    }

    public function reactivar(Paciente $paciente): Paciente
    {
        $paciente->update(['esta_activo' => true]);
        return $paciente->refresh();
    }

    public function findByCedula(string $cedula): ?Paciente
    {
        return Paciente::query()->where('cedula', $cedula)->first();
    }

    public function paginate(int $perPage = 25, string $estado = 'activos'): LengthAwarePaginator
    {
        $query = Paciente::query()
            ->orderBy('apellidos')
            ->orderBy('nombres');

        if ($estado === 'activos') {
            $query->where('esta_activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('esta_activo', false);
        }
        // 'todos' → sin filtro

        return $query->paginate($perPage);
    }
}
