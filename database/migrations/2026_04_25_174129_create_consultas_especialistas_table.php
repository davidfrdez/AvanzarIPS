<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

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

    }

    public function down(): void
    {
        Schema::dropIfExists('consultas_especialistas');
    }
};
