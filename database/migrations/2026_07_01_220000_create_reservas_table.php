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
    Schema::create('reservas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained('clientes');
        $table->dateTime('fecha_reserva')->useCurrent();
        $table->integer('cantidad');
        $table->decimal('total_pagar', 8, 2)->default(0);
        $table->string('estado')->default('pendiente');
        $table->boolean('estado_base')->default(1);
        $table->timestamps();
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('reservas');
    }
};
