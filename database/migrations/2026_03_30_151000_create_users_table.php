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
        Schema::create('usuarios', function (Blueprint $table) { // Cambié 'users' por 'usuarios'
            $table->id();
            $table->string('nombre'); // Cambié 'name' por 'nombre'
            $table->foreignId('rol_id')->constrained('roles'); // Esto exige que la tabla 'roles' se cree ANTES
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->string('correo')->unique(); // Cambié 'email' por 'correo'
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Recomendación: deja 'password' para que Laravel no se queje
            $table->boolean('esta_activo')->default(true); // Faltaba este campo de tu modelo
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
