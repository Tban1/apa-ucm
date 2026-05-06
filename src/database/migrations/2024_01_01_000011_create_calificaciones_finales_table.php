<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones_finales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->tinyInteger('puntaje_total')->unsigned();
            $table->enum('calificacion', ['muy_bueno', 'bueno', 'aceptable', 'deficiente']);
            $table->foreignUuid('determinada_por')->constrained('users')->restrictOnDelete();
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->boolean('es_apelacion')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones_finales');
    }
};
