<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\DTOs\HistoriaClinicaIngresoDTO;
use App\DTOs\PacienteDTO;
use App\Models\Paciente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PacienteServiceInterface
{
    public function create(PacienteDTO $paciente, ?HistoriaClinicaIngresoDTO $ingreso = null): Paciente;

    public function update(Paciente $paciente, PacienteDTO $dto): Paciente;

    public function softDelete(Paciente $paciente): bool;

    /**
     * Da de alta (da de baja clínica) al paciente: esta_activo = false.
     * El registro permanece visible en el historial; no es un soft-delete.
     */
    public function darAlta(Paciente $paciente): Paciente;

    /**
     * Reactiva un paciente previamente dado de alta: esta_activo = true.
     */
    public function reactivar(Paciente $paciente): Paciente;

    public function findByCedula(string $cedula): ?Paciente;

    /**
     * @param int    $perPage
     * @param string $estado  'activos' | 'inactivos' | 'todos'
     */
    public function paginate(int $perPage = 25, string $estado = 'activos'): LengthAwarePaginator;
}
