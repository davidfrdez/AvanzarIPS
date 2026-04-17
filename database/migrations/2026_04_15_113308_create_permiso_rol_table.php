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
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla roles
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            
            // Relación con la tabla permisos
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
            
            $table->timestamps();

            // Evitamos que se duplique la asignación de un mismo permiso a un mismo rol
            $table->unique(['rol_id', 'permiso_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};
