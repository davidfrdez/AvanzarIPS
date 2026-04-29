<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tablasConSoftDeletes = [
        'usuarios',
        'pacientes',
        'terapias',
        'historias_clinicas_ingreso',
        'consentimientos_legales',
        'ordenes_medicas',
        'consultas_especialistas',
        'escalas_weefim',
        'citas',
    ];

    public function up(): void
    {
        foreach ($this->tablasConSoftDeletes as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }

        if (Schema::hasTable('auditoria_cambios')) {
            Schema::table('auditoria_cambios', function (Blueprint $table): void {
                if (!Schema::hasColumn('auditoria_cambios', 'ip')) {
                    $table->string('ip', 45)->nullable()->after('detalles');
                }
                if (!Schema::hasColumn('auditoria_cambios', 'user_agent')) {
                    $table->string('user_agent', 500)->nullable()->after('ip');
                }
                $table->index(['nombre_tabla', 'registro_id'], 'idx_audit_target');
                $table->index('usuario_id', 'idx_audit_user');
                $table->index('created_at', 'idx_audit_created');
            });
        }

        if (Schema::hasTable('pacientes')) {
            Schema::table('pacientes', function (Blueprint $table): void {
                $table->index(['tipo_documento', 'cedula'], 'idx_pacientes_tipo_doc');
            });
        }

        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table): void {
                $table->index('programada_para', 'idx_citas_programada');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablasConSoftDeletes as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->dropSoftDeletes();
                });
            }
        }

        if (Schema::hasTable('auditoria_cambios')) {
            Schema::table('auditoria_cambios', function (Blueprint $table): void {
                $table->dropIndex('idx_audit_target');
                $table->dropIndex('idx_audit_user');
                $table->dropIndex('idx_audit_created');
                if (Schema::hasColumn('auditoria_cambios', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
                if (Schema::hasColumn('auditoria_cambios', 'ip')) {
                    $table->dropColumn('ip');
                }
            });
        }

        if (Schema::hasTable('pacientes')) {
            Schema::table('pacientes', function (Blueprint $table): void {
                $table->dropIndex('idx_pacientes_tipo_doc');
            });
        }

        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table): void {
                $table->dropIndex('idx_citas_programada');
            });
        }
    }
};
