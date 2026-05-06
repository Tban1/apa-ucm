<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->enum('etapa', [
                'carga_evidencias', 'evaluacion_secretario', 'evaluacion_cca',
                'apelaciones', 'evaluacion_jefatura', 'cierre',
            ]);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();

            $table->unique(['periodo_id', 'etapa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronogramas');
    }
};
