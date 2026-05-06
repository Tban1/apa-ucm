<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->smallInteger('anio')->unsigned();
            $table->string('nombre');
            $table->enum('estado', ['borrador', 'activo', 'en_evaluacion', 'cerrado'])->default('borrador');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->foreignUuid('creado_por')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
