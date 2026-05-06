<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignUuid('evaluador_id')->constrained('users')->restrictOnDelete();
            $table->tinyInteger('puntaje_docencia')->unsigned()->default(0);
            $table->tinyInteger('puntaje_investigacion')->unsigned()->default(0);
            $table->tinyInteger('puntaje_vinculacion')->unsigned()->default(0);
            $table->tinyInteger('puntaje_gestion')->unsigned()->default(0);
            $table->tinyInteger('puntaje_formacion')->unsigned()->default(0);
            $table->text('comentario')->nullable();
            $table->boolean('es_apelacion')->default(false);
            $table->timestamps();

            $table->unique(['nomina_id', 'evaluador_id', 'es_apelacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones');
    }
};
