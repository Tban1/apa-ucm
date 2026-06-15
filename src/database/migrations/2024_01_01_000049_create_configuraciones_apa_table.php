<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_apa', function (Blueprint $table) {
            $table->string('clave', 100)->primary();
            $table->text('valor');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        DB::table('configuraciones_apa')->insert([
            [
                // TODO (Valeria): confirmar si la jornada efectiva son 40h o cambia por resolución.
                // Cuando la jornada pase a 40h formales, actualizar este valor en la tabla.
                // Este valor es de REFERENCIA para mostrar advertencias, NO bloquea el ingreso.
                'clave'       => 'horas_semestre_base',
                'valor'       => '40',
                'descripcion' => 'Horas efectivas por semestre (base para advertencia de coherencia). Editable por CCDA.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                // TODO (Valeria): decidir cuántos decimales mostrar/almacenar.
                'clave'       => 'decimales_pct',
                'valor'       => '2',
                'descripcion' => 'Decimales para redondeo del porcentaje calculado desde horas.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_apa');
    }
};
