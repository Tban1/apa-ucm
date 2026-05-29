<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->decimal('puntaje_docencia', 3, 1)->default(1)->change();
            $table->decimal('puntaje_investigacion', 3, 1)->default(1)->change();
            $table->decimal('puntaje_vinculacion', 3, 1)->default(1)->change();
            $table->decimal('puntaje_gestion', 3, 1)->default(1)->change();
            $table->decimal('puntaje_formacion', 3, 1)->default(1)->change();
        });

        Schema::table('calificaciones_finales', function (Blueprint $table) {
            $table->decimal('nota_final', 3, 2)->nullable()->after('puntaje_total');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones_finales', function (Blueprint $table) {
            $table->dropColumn('nota_final');
        });

        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('puntaje_docencia')->default(0)->change();
            $table->unsignedTinyInteger('puntaje_investigacion')->default(0)->change();
            $table->unsignedTinyInteger('puntaje_vinculacion')->default(0)->change();
            $table->unsignedTinyInteger('puntaje_gestion')->default(0)->change();
            $table->unsignedTinyInteger('puntaje_formacion')->default(0)->change();
        });
    }
};
