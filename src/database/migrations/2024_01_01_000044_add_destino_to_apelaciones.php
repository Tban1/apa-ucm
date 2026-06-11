<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apelaciones', function (Blueprint $table) {
            $table->string('destino', 10)->default('cca')->after('estado');
        });

        DB::statement("ALTER TABLE apelaciones ADD CONSTRAINT apelaciones_destino_check
            CHECK (destino IN ('cca', 'ccda'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE apelaciones DROP CONSTRAINT IF EXISTS apelaciones_destino_check');

        Schema::table('apelaciones', function (Blueprint $table) {
            $table->dropColumn('destino');
        });
    }
};
