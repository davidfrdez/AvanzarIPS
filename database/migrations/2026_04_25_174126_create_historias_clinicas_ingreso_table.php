<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

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

    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas_ingreso');
    }
};
