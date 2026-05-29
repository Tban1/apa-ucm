<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('categoria_academica', ['titular', 'adjunto', 'auxiliar'])->nullable()->after('role');
            $table->enum('linea_desarrollo', ['docente', 'investigador', 'mixta'])->nullable()->after('categoria_academica');
            $table->date('fecha_jerarquizacion')->nullable()->after('linea_desarrollo');
            $table->unsignedSmallInteger('horas_contrato_isem')->nullable()->after('fecha_jerarquizacion');
            $table->unsignedSmallInteger('horas_contrato_iisem')->nullable()->after('horas_contrato_isem');
            $table->decimal('nota_anterior', 3, 2)->nullable()->after('horas_contrato_iisem');
            $table->string('concepto_anterior', 40)->nullable()->after('nota_anterior');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'categoria_academica', 'linea_desarrollo', 'fecha_jerarquizacion',
                'horas_contrato_isem', 'horas_contrato_iisem', 'nota_anterior', 'concepto_anterior',
            ]);
        });
    }
};
