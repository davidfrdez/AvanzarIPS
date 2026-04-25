<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objetivo_id')->constrained('objetivos')->onDelete('cascade');
            $table->string('nombre');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
