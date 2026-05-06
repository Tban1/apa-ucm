<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('rut')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->foreignUuid('facultad_id')->nullable()->nullOnDelete()->constrained('facultades');
            $table->foreignUuid('departamento_id')->nullable()->nullOnDelete()->constrained('departamentos');
            $table->enum('role', [
                'admin', 'analista_ccda', 'secretario',
                'miembro_cca', 'jefe_academico', 'academico',
            ])->default('academico');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
