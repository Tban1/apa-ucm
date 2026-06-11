<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE solicitudes DROP CONSTRAINT IF EXISTS solicitudes_tipo_check');
        DB::statement("ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_tipo_check CHECK (tipo::text = ANY (ARRAY[
            'licencia_medica'::text,
            'extension_plazo'::text,
            'perfeccionamiento'::text,
            'cargo_administrativo'::text,
            'otro'::text
        ]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE solicitudes DROP CONSTRAINT IF EXISTS solicitudes_tipo_check');
        DB::statement("ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_tipo_check CHECK (tipo::text = ANY (ARRAY[
            'licencia_medica'::text,
            'extension_plazo'::text
        ]))");
    }
};
