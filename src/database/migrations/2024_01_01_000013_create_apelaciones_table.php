<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apelaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->text('motivo');
            $table->enum('estado', ['solicitada', 'en_revision', 'resuelta', 'rechazada'])->default('solicitada');
            $table->date('fecha_solicitud');
            $table->date('fecha_resolucion')->nullable();
            $table->text('resolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apelaciones');
    }
};
