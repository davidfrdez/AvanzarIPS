<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>F-GDG-020 EVOLUCION DE PACIENTE - {{ $paciente->nombres }} {{ $paciente->apellidos }}</title>
<style>
    @page { margin: 18mm 14mm 16mm 14mm; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; margin: 0; }

    /* === Encabezado tipo F-GDG-020 === */
    table.header { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.header td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
    .logo-cell { width: 28%; text-align: center; }
    .logo-cell .logo { font-family: DejaVu Sans, sans-serif; font-weight: bold; font-size: 16px; color: #1a4f8b; }
    .logo-cell .logo small { display:block; font-size: 7px; font-weight: normal; color:#444; letter-spacing: 0.3px; }
    .title-cell { width: 44%; text-align: center; font-weight: bold; font-size: 12px; letter-spacing: 1px; }
    .code-cell { width: 14%; text-align: center; font-weight: bold; }
    .doc-cell { width: 14%; text-align: center; font-weight: bold; font-size: 9px; }

    /* === Recepcion === */
    table.recep { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.recep td, table.recep th {
        border: 1px solid #000; padding: 3px 6px; font-size: 9px; vertical-align: top;
    }
    table.recep th {
        background: #e9eef3; text-align: center; font-weight: bold; letter-spacing: 0.5px;
    }
    .label { font-weight: bold; font-size: 8px; color: #222; text-transform: uppercase; }
    .sex-box { display: inline-block; width: 14px; height: 14px; border: 1px solid #000;
               text-align: center; line-height: 14px; font-weight: bold; margin-right: 2px; font-size: 9px; }

    /* === Tabla de evoluciones === */
    table.evol { width: 100%; border-collapse: collapse; }
    table.evol th, table.evol td {
        border: 1px solid #000; padding: 4px 6px; vertical-align: top; font-size: 9px;
    }
    table.evol th {
        background: #e9eef3; text-align: center; font-weight: bold;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    table.evol td.fecha { width: 11%; text-align: center; white-space: nowrap; }
    table.evol td.hora  { width: 9%;  text-align: center; }
    table.evol td.area  { width: 12%; text-align: center; font-weight: bold; }
    table.evol td.body  { width: 68%; }

    .firma { margin-top: 6px; padding-top: 4px; border-top: 1px dashed #888;
             text-align: right; font-size: 8px; line-height: 1.35; }
    .firma .nombre { font-weight: bold; }
    .firma .ips { font-style: italic; }

    .pie { margin-top: 10px; font-size: 8px; color: #555; text-align: right; }
    .empty { text-align: center; padding: 12px; color: #666; font-style: italic; }
</style>
</head>
<body>

@php
    use Illuminate\Support\Carbon;

    // Calcular edad
    $edad = null;
    if (!empty($paciente->fecha_nacimiento)) {
        try {
            $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
        } catch (\Throwable $e) { $edad = null; }
    }

    $sexo = strtoupper((string) ($paciente->sexo ?? ''));
    $isM = str_starts_with($sexo, 'M');
    $isF = str_starts_with($sexo, 'F');

    // Diagnostico: tomar el ultimo registrado en historias de ingreso si existe
    $diagnostico = $paciente->historiasClinicasIngreso()->latest()->value('impresion_diagnostica')
        ?? $paciente->historiasClinicasIngreso()->latest()->value('motivo_consulta')
        ?? '';

    // Mapeo de especialidad -> abreviatura tipo PDF original
    $abreviarArea = function (?string $nombre): string {
        $n = mb_strtolower((string) $nombre);
        return match (true) {
            str_contains($n, 'psico') => 'PSICO',
            str_contains($n, 'fono')  => 'FONO',
            str_contains($n, 'fisio') => 'FISIO',
            str_contains($n, 'ocup')  => 'T.O.',
            str_contains($n, 'visi')  => 'VISIO',
            default => mb_strtoupper(mb_substr((string) $nombre, 0, 5)),
        };
    };
@endphp

<table class="header">
    <tr>
        <td class="logo-cell" rowspan="2">
            <div class="logo">Avanzar<small>IPS — Rehabilitacion y Habilitacion</small></div>
        </td>
        <td class="title-cell" rowspan="2">EVOLUCION DE PACIENTE</td>
        <td class="code-cell">F-GDG-020</td>
        <td class="doc-cell">Documento</td>
    </tr>
    <tr>
        <td class="code-cell">&nbsp;</td>
        <td class="doc-cell">CONTROLADO</td>
    </tr>
</table>

<table class="recep">
    <tr>
        <th colspan="6">RECEPCION</th>
    </tr>
    <tr>
        <td colspan="3">
            <div class="label">Nombre del Paciente</div>
            {{ mb_strtoupper(trim(($paciente->apellidos ?? '').' '.($paciente->nombres ?? ''))) }}
        </td>
        <td colspan="3">
            <div class="label">Numero de Identificacion</div>
            {{ $paciente->cedula }}
        </td>
    </tr>
    <tr>
        <td>
            <div class="label">EPS</div>
            {{ mb_strtoupper((string) ($paciente->eps ?? '')) }}
        </td>
        <td>
            <div class="label">Edad</div>
            {{ $edad !== null ? $edad.' AÑOS' : '—' }}
        </td>
        <td>
            <div class="label">Sexo</div>
            <span class="sex-box">M</span>{!! $isM ? '<span class="sex-box">X</span>' : '<span class="sex-box">&nbsp;</span>' !!}<span class="sex-box">F</span>{!! $isF ? '<span class="sex-box">X</span>' : '' !!}
        </td>
        <td colspan="3">
            <div class="label">Diagnostico</div>
            {{ mb_strtoupper((string) $diagnostico) ?: '—' }}
        </td>
    </tr>
</table>

{{-- Tabla de evoluciones --}}
<table class="evol">
    <thead>
        <tr>
            <th style="width:11%">Fecha</th>
            <th style="width:9%">Hora</th>
            <th style="width:12%">Area</th>
            <th style="width:68%">Atencion, Actividad y/o Procedimiento</th>
        </tr>
    </thead>
    <tbody>
    @forelse($terapias as $t)
        @php
            $fecha = optional($t->fecha_hora)->format('d-m-y');
            $hora  = optional($t->fecha_hora)->format('H:i');
            $area  = $abreviarArea($t->especialidad->nombre ?? '');
            $obj   = $t->objetivo->nombre ?? '';
            $act   = $t->actividad->nombre ?? '';

            // Construir descripcion estilo F-GDG-020
            $resp  = $t->resultados
                ->filter(fn ($r) => $r->marcado)
                ->map(fn ($r) => trim((string) ($r->respuesta->texto_predeterminado ?? '')))
                ->filter()
                ->implode('; ');

            $notas = $t->resultados
                ->map(fn ($r) => trim((string) ($r->notas_libres ?? '')))
                ->filter()
                ->implode(' ');

            $partes = [];
            if ($obj !== '') $partes[] = 'Inicia sesion con el objetivo de '.$obj.'.';
            if ($act !== '') $partes[] = 'Mediante la actividad: '.$act.'.';
            if ($resp !== '') $partes[] = $resp.'.';
            if ($notas !== '') $partes[] = $notas;
            if (empty($partes)) $partes[] = '—';
        @endphp
        <tr>
            <td class="fecha">{{ $fecha }}</td>
            <td class="hora">{{ $hora }}</td>
            <td class="area">{{ $area }}</td>
            <td class="body">
                {{ implode(' ', $partes) }}
                <div class="firma">
                    <div class="nombre">{{ $t->profesional->nombre ?? '—' }}</div>
                    <div>{{ optional($t->profesional)->correo }}</div>
                    <div>{{ $t->especialidad->nombre ?? '' }}</div>
                    <div class="ips">AVANZAR IPS</div>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="empty">Sin evoluciones registradas para este paciente.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="pie">
    Generado el {{ now()->format('d-m-Y H:i') }} — Avanzar IPS / Sistema de Historias Clinicas
</div>

</body>
</html>
