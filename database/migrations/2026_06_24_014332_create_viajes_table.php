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
    Schema::create('viajes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ruta_id')->constrained('rutas');
        $table->foreignId('bus_id')->constrained('buses');
        $table->date('fecha_viaje');
        $table->time('hora_salida');
        $table->string('estado')->default('programado');
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
