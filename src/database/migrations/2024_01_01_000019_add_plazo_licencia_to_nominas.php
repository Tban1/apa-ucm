<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            if (!Schema::hasColumn('nominas', 'plazo_licencia')) {
                $table->date('plazo_licencia')->nullable()->after('observacion_licencia');
            }
            if (!Schema::hasColumn('nominas', 'documento_licencia')) {
                $table->string('documento_licencia')->nullable()->after('plazo_licencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nominas', function (Blueprint $table) {
            $table->dropColumn(['plazo_licencia', 'documento_licencia']);
        });
    }
};
