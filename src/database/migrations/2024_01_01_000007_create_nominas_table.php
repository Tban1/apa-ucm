<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('estado', [
                'pendiente', 'en_carga', 'carga_cerrada',
                'en_evaluacion', 'evaluado', 'apelado', 'cerrado',
            ])->default('pendiente');
            $table->boolean('con_licencia')->default(false);
            $table->text('observacion_licencia')->nullable();
            $table->timestamps();

            $table->unique(['periodo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
