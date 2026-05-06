<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 60);
            $table->string('titulo');
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
