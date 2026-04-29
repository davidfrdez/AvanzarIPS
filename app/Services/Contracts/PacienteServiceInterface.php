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

    public function findByCedula(string $cedula): ?Paciente;

    public function paginate(int $perPage = 25): LengthAwarePaginator;
}
