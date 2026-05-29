<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE calificaciones_finales DROP CONSTRAINT IF EXISTS calificaciones_finales_calificacion_check');
        DB::statement("ALTER TABLE calificaciones_finales ADD CONSTRAINT calificaciones_finales_calificacion_check CHECK (calificacion::text = ANY (ARRAY['excelente'::text, 'muy_bueno'::text, 'bueno'::text, 'regular'::text, 'aceptable'::text, 'deficiente'::text]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE calificaciones_finales DROP CONSTRAINT IF EXISTS calificaciones_finales_calificacion_check');
        DB::statement("ALTER TABLE calificaciones_finales ADD CONSTRAINT calificaciones_finales_calificacion_check CHECK (calificacion::text = ANY (ARRAY['muy_bueno'::text, 'bueno'::text, 'aceptable'::text, 'deficiente'::text]))");
    }
};
