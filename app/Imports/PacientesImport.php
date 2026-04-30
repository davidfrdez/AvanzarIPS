<?php

declare(strict_types=1);

namespace App\Imports;

use App\DTOs\PacienteDTO;
use App\Enums\Sexo;
use App\Enums\TipoDocumento;
use App\Services\Contracts\PacienteServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Importador masivo de pacientes desde Excel.
 *
 * - Tope máximo: 500 filas (WithLimit).
 * - Política de errores: best-effort. Las filas válidas se insertan y las
 *   inválidas se registran en $errores con número de fila y mensajes.
 * - Reusa las mismas reglas de StorePacienteRequest (sin la sub-sección "ingreso")
 *   y delega la persistencia a PacienteService->create() para mantener una sola
 *   fuente de verdad.
 */
final class PacientesImport implements ToCollection, WithHeadingRow, WithLimit, WithChunkReading
{
    use Importable;

    public const LIMITE_FILAS = 500;

    /** @var array<int, array{fila:int, cedula:?string}> */
    public array $insertadas = [];

    /** @var array<int, array{fila:int, cedula:?string, errores: array<int|string, array<int, string>>}> */
    public array $errores = [];

    public int $totalFilas = 0;

    public function __construct(
        private readonly PacienteServiceInterface $pacienteService,
    ) {
    }

    public function limit(): int
    {
        return self::LIMITE_FILAS;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $idx => $row) {
            // Fila real en Excel: idx 0 == fila 2 (la 1 es el header).
            $filaExcel = (int) $idx + 2;
            $this->totalFilas++;

            /** @var array<string, mixed> $data */
            $data = is_array($row) ? $row : $row->toArray();
            $data = $this->normalizar($data);

            $cedula = isset($data['cedula']) ? (string) $data['cedula'] : null;

            // Saltar filas completamente vacías (no contar como error).
            if ($this->esFilaVacia($data)) {
                $this->totalFilas--;
                continue;
            }

            $validator = Validator::make($data, $this->reglas());

            if ($validator->fails()) {
                $this->errores[] = [
                    'fila' => $filaExcel,
                    'cedula' => $cedula,
                    'errores' => $validator->errors()->toArray(),
                ];
                continue;
            }

            try {
                $dto = PacienteDTO::fromArray($validator->validated());
                $modelo = $this->pacienteService->create($dto);
                $this->insertadas[] = [
                    'fila' => $filaExcel,
                    'cedula' => $modelo->cedula,
                ];
            } catch (Throwable $e) {
                $this->errores[] = [
                    'fila' => $filaExcel,
                    'cedula' => $cedula,
                    'errores' => ['general' => [$e->getMessage()]],
                ];
            }
        }
    }

    /**
     * Reglas equivalentes al bloque "paciente" de StorePacienteRequest.
     *
     * @return array<string, mixed>
     */
    private function reglas(): array
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
        ];
    }

    /**
     * Normaliza datos de Excel: trim, fechas seriales → Y-m-d, vacíos → null,
     * casts a string para evitar que cédulas numéricas lleguen como int.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizar(array $data): array
    {
        $stringFields = [
            'tipo_documento', 'cedula', 'nombres', 'apellidos', 'sexo',
            'direccion', 'barrio', 'telefono', 'correo', 'ocupacion', 'eps',
            'regimen_salud', 'categoria_eps', 'nombre_responsable',
            'telefono_responsable', 'parentesco_responsable',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $data)) {
                $valor = $data[$field];
                if ($valor === null || $valor === '') {
                    $data[$field] = null;
                } else {
                    $data[$field] = trim((string) $valor);
                    if ($data[$field] === '') {
                        $data[$field] = null;
                    }
                }
            }
        }

        if (isset($data['tipo_documento']) && is_string($data['tipo_documento'])) {
            $data['tipo_documento'] = strtoupper($data['tipo_documento']);
        }
        if (isset($data['sexo']) && is_string($data['sexo'])) {
            $data['sexo'] = strtoupper($data['sexo']);
        }

        if (isset($data['fecha_nacimiento']) && $data['fecha_nacimiento'] !== null) {
            $data['fecha_nacimiento'] = $this->parsearFecha($data['fecha_nacimiento']);
        }

        return $data;
    }

    /** Convierte fechas seriales de Excel o strings a "Y-m-d". */
    private function parsearFecha(mixed $valor): ?string
    {
        if (is_numeric($valor)) {
            try {
                return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $valor))
                    ->toDateString();
            } catch (Throwable) {
                return (string) $valor;
            }
        }

        try {
            return CarbonImmutable::parse((string) $valor)->toDateString();
        } catch (Throwable) {
            return (string) $valor;
        }
    }

    /** @param array<string, mixed> $data */
    private function esFilaVacia(array $data): bool
    {
        foreach ($data as $valor) {
            if ($valor !== null && $valor !== '' && $valor !== 0) {
                return false;
            }
        }
        return true;
    }
}
