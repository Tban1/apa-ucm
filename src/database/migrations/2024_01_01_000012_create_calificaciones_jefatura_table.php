<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones_jefatura', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignUuid('jefe_id')->constrained('users')->restrictOnDelete();
            $table->tinyInteger('puntaje')->unsigned();
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['nomina_id', 'jefe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones_jefatura');
    }
};
