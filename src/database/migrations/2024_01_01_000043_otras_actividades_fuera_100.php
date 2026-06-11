<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── evaluaciones: agregar bonus extra_otras_actividades ───────────
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->decimal('extra_otras_actividades', 3, 1)->default(0.0)->after('puntaje_formacion');
        });

        // ── compromisos_apa: redistribuir pct_otras y actualizar constraint ──
        // Soltar constraint ANTES de actualizar para evitar violaciones de redondeo
        DB::statement('ALTER TABLE compromisos_apa DROP CONSTRAINT IF EXISTS compromisos_apa_pct_suma_100');

        // Redistribuir pct_otras proporcionalmente en los 4 campos restantes
        $compromisos = DB::table('compromisos_apa')->where('pct_otras', '>', 0)->get();

        foreach ($compromisos as $c) {
            $total4 = $c->pct_docencia + $c->pct_investigacion
                    + $c->pct_extension + $c->pct_administracion;

            if ($total4 > 0) {
                $factor = (100 - 0) / $total4; // escalar los 4 campos a 100
                DB::table('compromisos_apa')->where('id', $c->id)->update([
                    'pct_docencia'       => round($c->pct_docencia       * $factor, 2),
                    'pct_investigacion'  => round($c->pct_investigacion  * $factor, 2),
                    'pct_extension'      => round($c->pct_extension      * $factor, 2),
                    'pct_administracion' => round($c->pct_administracion * $factor, 2),
                    'pct_otras'          => 0,
                ]);

                // Ajuste final por redondeo: asignar diferencia a pct_docencia
                $row = DB::table('compromisos_apa')->find($c->id);
                $suma = $row->pct_docencia + $row->pct_investigacion
                      + $row->pct_extension + $row->pct_administracion;
                if (abs($suma - 100) > 0.001) {
                    DB::table('compromisos_apa')->where('id', $c->id)->update([
                        'pct_docencia' => round($row->pct_docencia + (100 - $suma), 2),
                    ]);
                }
            } else {
                // Si solo había pct_otras, todo va a docencia
                DB::table('compromisos_apa')->where('id', $c->id)->update([
                    'pct_docencia' => 100,
                    'pct_otras'    => 0,
                ]);
            }
        }

        // Agregar nuevo CHECK: ahora los 4 campos deben sumar 100 (pct_otras siempre = 0)
        DB::statement('ALTER TABLE compromisos_apa ADD CONSTRAINT compromisos_apa_pct_suma_100 CHECK (
            pct_docencia + pct_investigacion + pct_extension + pct_administracion = 100
        )');
    }

    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropColumn('extra_otras_actividades');
        });

        DB::statement('ALTER TABLE compromisos_apa DROP CONSTRAINT IF EXISTS compromisos_apa_pct_suma_100');
        DB::statement('ALTER TABLE compromisos_apa ADD CONSTRAINT compromisos_apa_pct_suma_100 CHECK (
            pct_docencia + pct_investigacion + pct_extension +
            pct_administracion + pct_otras = 100
        )');
    }
};
