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
        Schema::create('encomiendas', function (Blueprint $table) {
        $table->id();

        $table->foreignId('cliente_id')->nullable()->constrained('clientes');
        $table->string('remitente_nombre')->nullable();
        $table->string('remitente_ci')->nullable();
        $table->string('remitente_telefono')->nullable();

        $table->string('destinatario_nombre');
        $table->string('destinatario_ci')->nullable();
        $table->string('destinatario_telefono')->nullable();

        $table->foreignId('viaje_id')->constrained('viajes');
        $table->foreignId('tipo_encomienda_id')->constrained('tipo_encomiendas');
        $table->integer('cantidad')->default(1);
        $table->decimal('total_pagar', 8, 2)->default(0);

        $table->string('estado')->default('recibido');
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
        });
    }

    public function down(): void
    {
    Schema::dropIfExists('encomiendas');
    }
};
