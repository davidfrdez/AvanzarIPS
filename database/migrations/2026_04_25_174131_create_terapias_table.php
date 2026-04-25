<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

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

    }

    public function down(): void
    {
        Schema::dropIfExists('terapias');
    }
};
