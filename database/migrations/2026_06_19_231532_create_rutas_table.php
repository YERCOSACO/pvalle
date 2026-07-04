<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('rutas', function (Blueprint $table) {
        $table->id();
        $table->string('origen');
        $table->string('destino');
        $table->decimal('distancia_km', 8, 2)->nullable();
        $table->decimal('precio_base', 8, 2);
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
