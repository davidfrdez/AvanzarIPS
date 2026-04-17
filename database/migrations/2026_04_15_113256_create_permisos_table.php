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
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ejemplo: 'Ver Pacientes', 'Editar Historias'
            $table->string('vista')->unique(); // Ejemplo: 'ver-pacientes' (Ideal para validaciones en código)
            $table->string('descripcion')->nullable(); // Opcional, para que sepas qué hace
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
