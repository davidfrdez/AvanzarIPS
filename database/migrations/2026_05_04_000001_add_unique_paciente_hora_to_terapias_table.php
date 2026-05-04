<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Protección a nivel de BD contra registros duplicados de terapia
 * para el mismo paciente en la misma franja horaria (hora en punto).
 *
 * Estrategia: columna virtual MySQL que extrae solo YYYY-MM-DD HH
 * + índice único sobre (paciente_id, fecha_hora_franja).
 *
 * NOTA: Solo aplica a MySQL/MariaDB. En SQLite (pruebas) se omite
 * porque las columnas generadas tienen sintaxis diferente; la capa
 * de aplicación (TerapiaController) cubre esa validación.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('terapias', function (Blueprint $table): void {
            // Columna virtual que trunca fecha_hora a la hora en punto (YYYY-MM-DD HH)
            $table->string('fecha_hora_franja', 14)
                ->virtualAs("DATE_FORMAT(fecha_hora, '%Y-%m-%d %H')")
                ->nullable()
                ->after('fecha_hora');

            // Índice único: un paciente no puede tener dos terapias en la misma franja
            $table->unique(
                ['paciente_id', 'fecha_hora_franja'],
                'terapias_paciente_franja_hora_unique'
            );
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('terapias', function (Blueprint $table): void {
            $table->dropUnique('terapias_paciente_franja_hora_unique');
            $table->dropColumn('fecha_hora_franja');
        });
    }
};
