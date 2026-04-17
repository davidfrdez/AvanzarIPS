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
        Schema::create('auditoria_cambios', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario que hace la acción
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade'); 

            $table->string('accion'); // Ej: CREAR, EDITAR, DESACTIVAR
            $table->string('nombre_tabla'); // Ej: usuarios, roles, pacientes
            $table->unsignedBigInteger('registro_id'); // El ID del elemento modificado
            $table->text('detalles'); // Explicación de lo que pasó
            
            $table->timestamps(); // Crea created_at y updated_at automáticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_cambios');
    }
};
