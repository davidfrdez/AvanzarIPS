<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\HistoriaClinicaIngresoDTO;
use App\DTOs\PacienteDTO;
use App\Enums\Sexo;
use App\Enums\TipoDocumento;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('pacientes.crear');
    }

    /** @return array<string, ValidationRule|array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'tipo_documento' => ['required', 'string', new Enum(TipoDocumento::class)],
            'cedula' => [
                'required', 'string', 'max:20',
                Rule::unique('pacientes', 'cedula')->whereNull('deleted_at'),
            ],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'sexo' => ['required', 'string', new Enum(Sexo::class)],
            'direccion' => ['required', 'string', 'max:255'],
            'barrio' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:30'],
            'correo' => ['nullable', 'email:rfc', 'max:150'],
            'ocupacion' => ['nullable', 'string', 'max:120'],
            'eps' => ['required', 'string', 'max:120'],
            'regimen_salud' => ['nullable', 'string', 'max:60'],
            'categoria_eps' => ['nullable', 'string', 'max:60'],
            'nombre_responsable' => ['nullable', 'string', 'max:200'],
            'telefono_responsable' => ['nullable', 'string', 'max:30'],
            'parentesco_responsable' => ['nullable', 'string', 'max:60'],

            'ingreso' => ['sometimes', 'array'],
            'ingreso.medico_id' => ['required_with:ingreso', 'integer', 'exists:usuarios,id'],
            'ingreso.motivo_consulta' => ['nullable', 'string', 'max:1000'],
            'ingreso.enfermedad_actual' => ['nullable', 'string'],
            'ingreso.anamnesis' => ['nullable', 'string'],
            'ingreso.ant_personales' => ['nullable', 'string'],
            'ingreso.ant_familiares' => ['nullable', 'string'],
            'ingreso.ant_quirurgicos' => ['nullable', 'string'],
            'ingreso.ant_patologicos' => ['nullable', 'string'],
            'ingreso.ant_farmacologicos' => ['nullable', 'string'],
            'ingreso.ant_ginecolologicos' => ['nullable', 'string'],
            'ingreso.impresion_diagnostica' => ['nullable', 'string'],
            'ingreso.origen_enfermedad' => ['nullable', 'string', 'max:120'],
            'ingreso.plan_tratamiento' => ['nullable', 'string'],
            'ingreso.pronostico' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cedula.unique' => 'Ya existe un paciente registrado con este número de documento.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a hoy.',
            'ingreso.medico_id.exists' => 'El médico asignado al ingreso no existe.',
        ];
    }

    public function toPacienteDTO(): PacienteDTO
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();
        return PacienteDTO::fromArray($validated);
    }

    public function toIngresoDTO(): ?HistoriaClinicaIngresoDTO
    {
        /** @var array<string, mixed>|null $ingreso */
        $ingreso = $this->validated('ingreso');
        return is_array($ingreso) ? HistoriaClinicaIngresoDTO::fromArray($ingreso) : null;
    }
}
