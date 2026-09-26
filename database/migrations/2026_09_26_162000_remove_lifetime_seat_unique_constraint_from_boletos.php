<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->index('viaje_id', 'boletos_viaje_id_index');
            $table->dropUnique('boletos_viaje_id_numero_asiento_unique');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->unique(['viaje_id', 'numero_asiento']);
            $table->dropIndex('boletos_viaje_id_index');
        });
    }
};
