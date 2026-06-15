<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semestres_academicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->integer('numero')->comment('1 o 2');
            $table->date('fecha_cierre');
            $table->timestamps();

            $table->unique(['periodo_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semestres_academicos');
    }
};
