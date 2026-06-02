<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compromisos_apa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academico_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->enum('area', ['docencia', 'investigacion', 'extension', 'administracion', 'otras']);
            $table->decimal('porcentaje', 5, 2);
            $table->string('semestre_negociacion', 10);
            $table->timestamps();

            $table->unique(['academico_id', 'periodo_id', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compromisos_apa');
    }
};
