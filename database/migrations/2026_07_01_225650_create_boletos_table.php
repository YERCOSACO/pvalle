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
    Schema::create('boletos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('reserva_id')->constrained('reservas');
        $table->foreignId('viaje_id')->constrained('viajes');
        $table->string('numero_asiento');
        $table->string('nombre_pasajero');
        $table->string('ci_pasajero')->nullable();
        $table->string('telefono_pasajero')->nullable();
        $table->decimal('precio', 8, 2);
        $table->string('metodo_pago')->default('QR'); // QR, efectivo, tarjeta
        $table->string('estado')->default('pendiente'); // pendiente, confirmado, cancelado
        $table->timestamp('expira_en')->nullable(); // 15 min desde creación si estado=pendiente
        $table->boolean('estado_base')->default(1);
        $table->timestamps();

        // Evita que el mismo asiento se venda 2 veces en el mismo viaje
        $table->unique(['viaje_id', 'numero_asiento']);
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('boletos');
    }
};
