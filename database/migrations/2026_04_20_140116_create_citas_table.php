<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla pacientes
            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->onDelete('cascade');

            // Relación con la tabla usuarios (el médico que atiende)
            $table->foreignId('medico_id')
                ->constrained('usuarios')
                ->onDelete('cascade');

            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->dateTime('programada_para'); // Fecha y hora exacta de la cita

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
