<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar constraint de suma = 100
        DB::statement('ALTER TABLE compromisos_apa DROP CONSTRAINT IF EXISTS compromisos_apa_pct_suma_100');

        // 2. Migrar datos existentes: '1' → 'S1', '2' → 'S2'
        DB::table('compromisos_apa')
            ->where('semestre', '1')
            ->update(['semestre' => 'S1']);

        DB::table('compromisos_apa')
            ->where('semestre', '2')
            ->update(['semestre' => 'S2']);

        // 3. Eliminar semestres 3 y 4 (ya no se usan)
        DB::table('compromisos_apa')
            ->whereIn('semestre', ['3', '4'])
            ->delete();

        // 4. Modificar unique constraint para incluir periodo_id
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->dropUnique(['nomina_id', 'semestre']);
        });

        // 5. Modificar columna semestre a enum
        DB::statement("ALTER TABLE compromisos_apa 
            ALTER COLUMN semestre TYPE varchar(2),
            ADD CONSTRAINT compromisos_apa_semestre_check 
            CHECK (semestre IN ('S1', 'S2'))");

        // 6. Agregar nuevo unique constraint con periodo_id
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->unique(['nomina_id', 'periodo_id', 'semestre']);
        });
    }

    public function down(): void
    {
        // Revertir cambios
        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->dropUnique(['nomina_id', 'periodo_id', 'semestre']);
        });

        DB::statement('ALTER TABLE compromisos_apa DROP CONSTRAINT IF EXISTS compromisos_apa_semestre_check');

        DB::statement("ALTER TABLE compromisos_apa ALTER COLUMN semestre TYPE varchar(10)");

        // Migrar de vuelta a números
        DB::table('compromisos_apa')
            ->where('semestre', 'S1')
            ->update(['semestre' => '1']);

        DB::table('compromisos_apa')
            ->where('semestre', 'S2')
            ->update(['semestre' => '2']);

        Schema::table('compromisos_apa', function (Blueprint $table) {
            $table->unique(['nomina_id', 'semestre']);
        });

        // Restaurar constraint de suma 100
        DB::statement('ALTER TABLE compromisos_apa ADD CONSTRAINT compromisos_apa_pct_suma_100 CHECK (
            pct_docencia + pct_investigacion + pct_extension + pct_administracion + pct_otras = 100
        )');
    }
};
