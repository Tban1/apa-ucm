<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignUuid('categoria_id')->constrained('categorias_apa')->restrictOnDelete();
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->unsignedInteger('tamano');
            $table->string('mime_type', 100)->nullable();
            $table->foreignUuid('subido_por')->constrained('users')->restrictOnDelete();
            $table->boolean('es_apelacion')->default(false);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
