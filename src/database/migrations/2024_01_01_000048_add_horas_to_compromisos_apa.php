<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compromisos_apa', function (Blueprint $table) {
            // Horas declaradas por área (input crudo del académico).
            // Nullable: filas legacy (fuente='sapd' o importadas) pueden no tener horas.
            // Los pct_* siguen siendo el campo canónico que usa la fórmula de nota.
            $table->decimal('hrs_docencia',       6, 2)->nullable()->after('pct_otras');
            $table->decimal('hrs_investigacion',  6, 2)->nullable()->after('hrs_docencia');
            $table->decimal('hrs_extension',      6, 2)->nullable()->after('hrs_investigacion');
            $table->decimal('hrs_administracion', 6, 2)->nullable()->after('hrs_extension');
            // "Otras actividades" va FUERA del 100%; lo valida otro actor (CCDA), no el académico.
            $table->decimal('hrs_otras',          6, 2)->nullable()->after('hrs_administracion');
        });
    }

    public function down(): void
    {
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->dropColumn([
                'hrs_docencia', 'hrs_investigacion', 'hrs_extension',
                'hrs_administracion', 'hrs_otras',
            ]);
        });
    }
};
