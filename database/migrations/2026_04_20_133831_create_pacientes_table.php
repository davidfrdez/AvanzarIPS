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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento');
            $table->string('cedula')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento');
            $table->string('sexo');
            $table->string('direccion');
            $table->string('barrio');
            $table->string('telefono');
            $table->string('correo')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('eps');
            $table->string('regimen_salud')->nullable();
            $table->string('categoria_eps')->nullable();
            
            // Datos del responsable / acompañante
            $table->string('nombre_responsable')->nullable();
            $table->string('telefono_responsable')->nullable();
            $table->string('parentesco_responsable')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
