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
    Schema::create('clientes', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellido');
        $table->string('cedula')->unique();
        $table->string('email')->unique();
        $table->string('contrasena');
        $table->string('telefono')->nullable();
        $table->string('direccion')->nullable();
        $table->date('fecha_nacimiento')->nullable();
        $table->rememberToken();
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
