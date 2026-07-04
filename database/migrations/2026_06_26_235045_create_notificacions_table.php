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
    Schema::create('notificaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained('clientes');
        $table->string('titulo');
        $table->text('mensaje');
        $table->string('tipo')->default('info');        // info, alerta, urgente
        $table->string('canal')->default('sistema');     // sistema, email, sms
        $table->string('prioridad')->default('normal');  // baja, normal, alta
        $table->timestamp('fecha_envio')->nullable();
        $table->timestamp('fecha_lectura')->nullable();
        $table->nullableMorphs('referenciable');
        $table->string('estado')->default('pendiente');  // pendiente, enviada, leida
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('notificaciones');
}
};
