<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

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

    }

    public function down(): void
    {
        Schema::dropIfExists('escalas_weefim');
    }
};
