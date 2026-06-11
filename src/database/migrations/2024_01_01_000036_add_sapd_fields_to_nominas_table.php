<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            if (!Schema::hasColumn('nominas', 'numero_personal')) {
                $table->string('numero_personal', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('nominas', 'rut')) {
                $table->string('rut', 12)->nullable()->after('numero_personal');
            }
            if (!Schema::hasColumn('nominas', 'nombre')) {
                $table->string('nombre', 255)->nullable()->after('rut');
            }
            if (!Schema::hasColumn('nominas', 'adscripcion_academica')) {
                $table->string('adscripcion_academica', 100)->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('nominas', 'unidad_superior')) {
                $table->string('unidad_superior', 150)->nullable()->after('adscripcion_academica');
            }
            if (!Schema::hasColumn('nominas', 'unidad')) {
                $table->string('unidad', 150)->nullable()->after('unidad_superior');
            }
            if (!Schema::hasColumn('nominas', 'nombre_posicion')) {
                $table->string('nombre_posicion', 150)->nullable()->after('unidad');
            }
            if (!Schema::hasColumn('nominas', 'tipo_trabajador')) {
                $table->string('tipo_trabajador', 50)->nullable()->after('nombre_posicion');
            }
            if (!Schema::hasColumn('nominas', 'fecha_inicio_contrato')) {
                $table->date('fecha_inicio_contrato')->nullable()->after('tipo_trabajador');
            }
            if (!Schema::hasColumn('nominas', 'fecha_categorizacion')) {
                $table->date('fecha_categorizacion')->nullable()->after('categoria');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            $table->dropColumn([
                'numero_personal', 'rut', 'nombre', 'adscripcion_academica',
                'unidad_superior', 'unidad', 'nombre_posicion', 'tipo_trabajador',
                'fecha_inicio_contrato', 'fecha_categorizacion',
            ]);
        });
    }
};
