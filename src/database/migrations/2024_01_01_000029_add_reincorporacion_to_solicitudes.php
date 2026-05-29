<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->timestamp('fecha_reincorporacion')->nullable()->after('motivo_rechazo');
            $table->foreignUuid('reincorporado_por')->nullable()->after('fecha_reincorporacion')
                ->constrained('users')->nullOnDelete();
            $table->text('motivo_reincorporacion')->nullable()->after('reincorporado_por');
            $table->date('nuevo_plazo_evidencias')->nullable()->after('motivo_reincorporacion');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reincorporado_por');
            $table->dropColumn([
                'fecha_reincorporacion',
                'motivo_reincorporacion',
                'nuevo_plazo_evidencias',
            ]);
        });
    }
};
