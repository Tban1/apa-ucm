<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cronogramas DROP CONSTRAINT IF EXISTS cronogramas_etapa_check');
        DB::statement("ALTER TABLE cronogramas ADD CONSTRAINT cronogramas_etapa_check CHECK (etapa IN (
            'carga_evidencias','evaluacion_secretario','evaluacion_cca',
            'consejo_facultad','apelaciones','revision_vicerrectoria','cierre'
        ))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cronogramas DROP CONSTRAINT IF EXISTS cronogramas_etapa_check');
        DB::statement("ALTER TABLE cronogramas ADD CONSTRAINT cronogramas_etapa_check CHECK (etapa IN (
            'carga_evidencias','evaluacion_secretario','evaluacion_cca',
            'apelaciones','evaluacion_jefatura','cierre'
        ))");
    }
};
