<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE actas DROP CONSTRAINT IF EXISTS actas_tipo_check");
        DB::statement("ALTER TABLE actas ADD CONSTRAINT actas_tipo_check CHECK (tipo::text = ANY (ARRAY['evaluacion'::text, 'apelacion'::text, 'cierre_proceso'::text]))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE actas DROP CONSTRAINT IF EXISTS actas_tipo_check");
        DB::statement("ALTER TABLE actas ADD CONSTRAINT actas_tipo_check CHECK (tipo::text = ANY (ARRAY['evaluacion'::text, 'apelacion'::text]))");
    }
};
