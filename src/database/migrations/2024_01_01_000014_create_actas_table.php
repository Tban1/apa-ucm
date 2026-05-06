<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->foreignUuid('facultad_id')->constrained('facultades')->restrictOnDelete();
            $table->string('archivo')->nullable();
            $table->foreignUuid('generada_por')->constrained('users')->restrictOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['evaluacion', 'apelacion'])->default('evaluacion');
            $table->timestamps();

            $table->unique(['periodo_id', 'facultad_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};
