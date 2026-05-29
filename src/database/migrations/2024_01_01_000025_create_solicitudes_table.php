<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->enum('tipo', ['licencia_medica', 'extension_plazo']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('motivo');
            $table->string('documento_adjunto')->nullable();
            $table->enum('estado', ['activa', 'cerrada'])->default('activa');
            $table->foreignUuid('creado_por')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['nomina_id', 'estado']);
            $table->index(['tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
