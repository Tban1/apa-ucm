<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Soltar constraint antes de renombrar etapas para evitar violación
        DB::statement('ALTER TABLE cronogramas DROP CONSTRAINT IF EXISTS cronogramas_etapa_check');

        DB::table('cronogramas')->where('etapa', 'consejo_facultad')->update(['etapa' => 'comunicacion_resultados']);
        DB::table('cronogramas')->where('etapa', 'cierre')->delete();

        DB::statement("ALTER TABLE cronogramas ADD CONSTRAINT cronogramas_etapa_check CHECK (etapa IN (
            'carga_evidencias','validacion_secretario','informe_jefatura',
            'evaluacion_cca','comunicacion_resultados','apelaciones',
            'registro_ccda','revision_vicerrectoria'
        ))");
    }

    public function down(): void
    {
        DB::table('cronogramas')->where('etapa', 'comunicacion_resultados')->update(['etapa' => 'consejo_facultad']);

        DB::statement('ALTER TABLE cronogramas DROP CONSTRAINT IF EXISTS cronogramas_etapa_check');
        DB::statement("ALTER TABLE cronogramas ADD CONSTRAINT cronogramas_etapa_check CHECK (etapa IN (
            'carga_evidencias','validacion_secretario','evaluacion_cca',
            'consejo_facultad','apelaciones','revision_vicerrectoria','cierre'
        ))");
    }
};
