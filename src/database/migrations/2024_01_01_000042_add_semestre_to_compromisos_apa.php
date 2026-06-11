<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->string('semestre', 10)->default('1')->after('periodo_id');
        });

        // Todos los registros existentes son del semestre 1
        DB::table('compromisos_apa')->update(['semestre' => '1']);

        // Reemplazar unique(nomina_id) por unique(nomina_id, semestre)
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->dropUnique(['nomina_id']);
            $table->unique(['nomina_id', 'semestre']);
        });
    }

    public function down(): void
    {
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->dropUnique(['nomina_id', 'semestre']);
            $table->dropColumn('semestre');
            $table->unique(['nomina_id']);
        });
    }
};
