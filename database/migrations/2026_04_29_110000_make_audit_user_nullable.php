<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite usuario_id NULL en auditoria_cambios para registrar
 * operaciones del sistema (Jobs, comandos artisan, seeders, tinker).
 *
 * Requiere doctrine/dbal. Si no está instalado, ejecutar:
 *   composer require doctrine/dbal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria_cambios', function (Blueprint $table): void {
            $table->foreignId('usuario_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_cambios', function (Blueprint $table): void {
            $table->foreignId('usuario_id')->nullable(false)->change();
        });
    }
};
