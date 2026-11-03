<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->timestamp('fecha_emision')->nullable()->after('reserva_id');
            $table->foreignId('usuario_id')
                ->nullable()
                ->after('fecha_emision')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('cliente_emisor_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('clientes')
                ->nullOnDelete();
        });

        DB::table('boletos')
            ->where('estado', 'confirmado')
            ->whereNull('fecha_emision')
            ->update(['fecha_emision' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropForeign(['cliente_emisor_id']);
            $table->dropColumn(['fecha_emision', 'usuario_id', 'cliente_emisor_id']);
        });
    }
};
