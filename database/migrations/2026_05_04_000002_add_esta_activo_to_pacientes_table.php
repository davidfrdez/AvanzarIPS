<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el estado clínico del paciente (activo / dado de alta).
 *
 * `esta_activo = true`  → paciente en tratamiento activo.
 * `esta_activo = false` → paciente dado de alta (tratamiento concluido o inactivo).
 *
 * Todos los pacientes existentes se marcan como activos en la migración.
 * Distinto del soft-delete (`deleted_at`): un paciente dado de alta permanece
 * visible en el historial y sus registros clínicos son consultables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table): void {
            $table->boolean('esta_activo')->default(true)->after('parentesco_responsable');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table): void {
            $table->dropColumn('esta_activo');
        });
    }
};
