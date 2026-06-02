<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentarios_vicerrectora', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluacion_id')->constrained('evaluaciones')->cascadeOnDelete();
            $table->text('comentario');
            $table->foreignUuid('creado_por')->constrained('users');
            $table->timestamps();

            $table->unique('evaluacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comentarios_vicerrectora');
    }
};
