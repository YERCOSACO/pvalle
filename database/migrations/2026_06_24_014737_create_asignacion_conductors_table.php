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
    Schema::create('asignacion_conductores', function (Blueprint $table) {
        $table->id();
        $table->foreignId('viaje_id')->constrained('viajes');
        $table->foreignId('conductor_id')->constrained('conductores');
        $table->string('tipo_asignacion');
        $table->boolean('estado_base')->default(1);
        $table->timestamps();

        $table->unique(['viaje_id', 'tipo_asignacion']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_conductors');
    }
};
