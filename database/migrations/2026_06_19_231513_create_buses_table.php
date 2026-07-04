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
    Schema::create('buses', function (Blueprint $table) {
        $table->id();
        $table->string('placa')->unique();
        $table->integer('capacidad');
        $table->string('modelo')->nullable();
        $table->string('tipo_bus')->nullable();
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
