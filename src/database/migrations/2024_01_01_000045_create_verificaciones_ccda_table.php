<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificaciones_ccda', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->foreignUuid('facultad_id')->constrained('facultades')->cascadeOnDelete();
            $table->foreignUuid('verificado_por')->constrained('users');
            $table->boolean('doc_fisica_archivada')->default(false);
            $table->boolean('notas_comunicadas')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamp('verificado_en')->nullable();
            $table->timestamps();

            $table->unique(['periodo_id', 'facultad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificaciones_ccda');
    }
};
