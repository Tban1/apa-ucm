<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->foreignUuid('iniciada_por')->nullable()->after('creado_por')
                ->constrained('users')->restrictOnDelete();
            $table->foreignUuid('aprobada_por')->nullable()->after('iniciada_por')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable()->after('aprobada_por');
            $table->text('motivo_rechazo')->nullable()->after('fecha_aprobacion');
        });

        DB::table('solicitudes')->update([
            'iniciada_por' => DB::raw('creado_por'),
        ]);

        DB::table('solicitudes')->where('estado', 'activa')->update([
            'aprobada_por'     => DB::raw('creado_por'),
            'fecha_aprobacion' => DB::raw('created_at'),
        ]);

        DB::statement('ALTER TABLE solicitudes DROP CONSTRAINT IF EXISTS solicitudes_estado_check');
        DB::statement("ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_estado_check CHECK (estado::text = ANY (ARRAY['pendiente_aprobacion'::text, 'activa'::text, 'cerrada'::text, 'rechazada'::text]))");
        DB::statement("ALTER TABLE solicitudes ALTER COLUMN estado SET DEFAULT 'pendiente_aprobacion'");
    }

    public function down(): void
    {
        DB::table('solicitudes')->whereIn('estado', ['pendiente_aprobacion', 'rechazada'])
            ->update(['estado' => 'cerrada']);

        DB::statement('ALTER TABLE solicitudes DROP CONSTRAINT IF EXISTS solicitudes_estado_check');
        DB::statement("ALTER TABLE solicitudes ADD CONSTRAINT solicitudes_estado_check CHECK (estado::text = ANY (ARRAY['activa'::text, 'cerrada'::text]))");
        DB::statement("ALTER TABLE solicitudes ALTER COLUMN estado SET DEFAULT 'activa'");

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('iniciada_por');
            $table->dropConstrainedForeignId('aprobada_por');
            $table->dropColumn(['fecha_aprobacion', 'motivo_rechazo']);
        });
    }
};
