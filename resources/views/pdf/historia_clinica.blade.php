<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica - {{ $paciente->nombres }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .header h1 { color: #0056b3; margin: 0; font-size: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { background: #f4f4f4; padding: 5px; font-weight: bold; border-left: 3px solid #0056b3; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grid th, .grid td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .terapia-box { border: 1px solid #ccc; margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .terapia-header { font-weight: bold; color: #444; border-bottom: 1px dashed #ccc; padding-bottom: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Avanzar IPS - Historia Clínica</h1>
        <p>Documento Legal Electrónico</p>
    </div>

    <!-- DATOS DEL PACIENTE -->
    <div class="section">
        <div class="section-title">Datos del Paciente</div>
        <table class="grid">
            <tr>
                <th>Nombres:</th> <td>{{ $paciente->nombres }} {{ $paciente->apellidos }}</td>
                <th>Documento:</th> <td>{{ $paciente->tipo_documento }} {{ $paciente->cedula }}</td>
            </tr>
            <tr>
                <th>Fecha Nacimiento:</th> <td>{{ $paciente->fecha_nacimiento }}</td>
                <th>EPS:</th> <td>{{ $paciente->eps }} ({{ $paciente->regimen_salud }})</td>
            </tr>
            <tr>
                <th>Contacto:</th> <td>{{ $paciente->telefono }} - {{ $paciente->correo }}</td>
                <th>Responsable:</th> <td>{{ $paciente->nombre_responsable }} ({{ $paciente->parentesco_responsable }})</td>
            </tr>
        </table>
    </div>

    <!-- EVOLUCIONES CLINICAS -->
    <div class="section">
        <div class="section-title">Evoluciones Clínicas (Terapias)</div>
        
        @forelse($terapias as $terapia)
            <div class="terapia-box">
                <div class="terapia-header">
                    Fecha: {{ $terapia->fecha_hora->format('Y-m-d H:i') }} | 
                    Especialidad: {{ $terapia->especialidad->nombre ?? 'N/A' }} | 
                    Profesional: {{ $terapia->profesional->nombre ?? 'N/A' }}
                </div>
                <p><strong>Objetivo:</strong> {{ $terapia->objetivo->nombre ?? '' }}</p>
                <p><strong>Actividad Realizada:</strong> {{ $terapia->actividad->nombre ?? '' }}</p>
                
                <table class="grid" style="margin-top:5px;">
                    <tr><th>Criterio Evaluado</th><th>Marcado</th><th>Notas Relevantes</th></tr>
                    @foreach($terapia->resultados as $resultado)
                        <tr>
                            <td>{{ $resultado->respuesta->texto_predeterminado ?? 'N/A' }}</td>
                            <td style="text-align:center;">{{ $resultado->marcado ? 'Sí' : 'No' }}</td>
                            <td>{{ $resultado->notas_libres }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @empty
            <p>No hay evoluciones de terapia registradas para el paciente.</p>
        @endforelse
    </div>

</body>
</html>
