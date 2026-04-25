<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('asignaciones_objetivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objetivo_id')->constrained('objetivos')->onDelete('cascade');
            $table->foreignId('rol_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_objetivos');
    }
};
