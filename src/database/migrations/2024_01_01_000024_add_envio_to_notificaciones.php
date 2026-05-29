<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->enum('estado_envio', ['pendiente', 'enviado', 'fallido'])->nullable()->after('url');
            $table->timestamp('fecha_envio')->nullable()->after('estado_envio');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropColumn(['estado_envio', 'fecha_envio']);
        });
    }
};
