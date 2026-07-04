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
    Schema::create('incidencia_viajes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('viaje_id')->constrained('viajes');
        $table->foreignId('tipo_incidencia_id')->constrained('tipo_incidencias');
        $table->dateTime('fecha_inicio');
        $table->dateTime('fecha_fin')->nullable();
        $table->text('descripcion_detalle')->nullable();
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('incidencia_viajes');
    }
};
