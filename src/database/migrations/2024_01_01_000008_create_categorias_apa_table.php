<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_apa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('slug', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->tinyInteger('orden')->unsigned()->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_apa');
    }
};
