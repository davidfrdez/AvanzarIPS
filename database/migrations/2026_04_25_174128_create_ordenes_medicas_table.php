<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('ordenes_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('usuarios');
            $table->text('descripcion');
            $table->date('fecha_orden');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_medicas');
    }
};
