<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('consentimientos_legales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('tipo_consentimiento');
            $table->string('estado');
            $table->boolean('firmado_por_representante')->default(false);
            $table->string('nombre_firmante')->nullable();
            $table->string('documento_firmante')->nullable();
            $table->date('fecha_firma');
            $table->timestamp('created_at')->nullable();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('consentimientos_legales');
    }
};
