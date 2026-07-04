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
    Schema::create('asiento_viajes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('viaje_id')->constrained('viajes')->onDelete('cascade');
        $table->string('numero_asiento');
        $table->string('estado')->default('disponible'); // disponible, ocupado, bloqueado
        $table->boolean('estado_base')->default(1);
        $table->timestamps();

        $table->unique(['viaje_id', 'numero_asiento']);
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('asiento_viajes');
    }
};
