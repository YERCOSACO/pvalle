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
    Schema::create('conductores', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellido');
        $table->string('licencia')->unique();
        $table->string('telefono')->nullable();
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }
    public function down(): void
    {
    Schema::dropIfExists('conductores');
    }
};
