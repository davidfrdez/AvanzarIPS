import os
import datetime

migrations_dir = r"c:\Users\santi\Proyectos\IPS\AvanzarIPS\database\migrations"

tables_code = {
    "objetivos": """
        Schema::create('objetivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
""",
    "asignaciones_objetivos": """
        Schema::create('asignaciones_objetivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objetivo_id')->constrained('objetivos')->onDelete('cascade');
            $table->foreignId('rol_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('cascade');
        });
""",
    "actividades": """
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objetivo_id')->constrained('objetivos')->onDelete('cascade');
            $table->string('nombre');
        });
""",
    "respuestas": """
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->onDelete('cascade');
            $table->text('texto_predeterminado')->nullable();
        });
""",
    "historias_clinicas_ingreso": """
        Schema::create('historias_clinicas_ingreso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('usuarios');
            $table->text('motivo_consulta');
            $table->text('enfermedad_actual');
            $table->text('anamnesis');
            $table->text('ant_personales')->nullable();
            $table->text('ant_familiares')->nullable();
            $table->text('ant_quirurgicos')->nullable();
            $table->text('ant_patologicos')->nullable();
            $table->text('ant_farmacologicos')->nullable();
            $table->text('ant_ginecolologicos')->nullable();
            $table->text('impresion_diagnostica');
            $table->string('origen_enfermedad');
            $table->text('plan_tratamiento');
            $table->text('pronostico');
            $table->timestamps();
        });
""",
    "consentimientos_legales": """
        Schema::create('consentimientos_legales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('tipo_consentimiento');
            $table->string('estado');
            $table->boolean('firmado_por_representante')->default(false);
            $table->string('nombre_firmante')->nullable();
            $table->string('documento_firmante')->nullable();
            $table->date('fecha_firma');
            $table->timestamp('created_at')->nullable();
        });
""",
    "ordenes_medicas": """
        Schema::create('ordenes_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('usuarios');
            $table->text('descripcion');
            $table->date('fecha_orden');
            $table->timestamps();
        });
""",
    "consultas_especialistas": """
        Schema::create('consultas_especialistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('usuarios');
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->text('motivo_consulta');
            $table->text('examen_mental')->nullable();
            $table->text('diagnostico');
            $table->text('concepto');
            $table->string('escala_eeag')->nullable();
            $table->string('firma_electronica');
            $table->timestamp('fecha_hora');
            $table->timestamp('created_at')->nullable();
        });
""",
    "escalas_weefim": """
        Schema::create('escalas_weefim', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('usuarios');
            $table->date('fecha_evaluacion');
            $table->integer('subtotal_autocuidado');
            $table->integer('subtotal_movilidad');
            $table->integer('subtotal_cognicion');
            $table->integer('puntaje_total');
            $table->decimal('porcentaje_funcionalidad', 5, 2);
            $table->timestamp('created_at')->nullable();
        });
""",
    "terapias": """
        Schema::create('terapias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('usuarios');
            $table->foreignId('objetivo_id')->constrained('objetivos');
            $table->foreignId('actividad_id')->constrained('actividades');
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->string('firma_electronica');
            $table->timestamp('fecha_hora');
            $table->timestamps();
        });
""",
    "resultados_terapias": """
        Schema::create('resultados_terapias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terapia_id')->constrained('terapias')->onDelete('cascade');
            $table->foreignId('respuesta_id')->constrained('respuestas');
            $table->boolean('marcado')->default(false);
            $table->text('notas_libres')->nullable();
        });
"""
}

template = """<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{{
    public function up(): void
    {{
{up_code}
    }}

    public function down(): void
    {{
        Schema::dropIfExists('{table_name}');
    }}
}};
"""

base_time = datetime.datetime.now()
counter = 0

for table_name, schema_code in tables_code.items():
    counter += 1
    t = base_time + datetime.timedelta(seconds=counter)
    filename = f"{t.strftime('%Y_%m_%d_%H%M%S')}_create_{table_name}_table.php"
    filepath = os.path.join(migrations_dir, filename)
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(template.format(table_name=table_name, up_code=schema_code))
    print(f"Created {filename}")
