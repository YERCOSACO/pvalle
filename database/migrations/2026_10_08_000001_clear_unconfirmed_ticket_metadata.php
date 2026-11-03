<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('boletos')
            ->where('estado', '!=', 'confirmado')
            ->update([
                'fecha_emision' => null,
                'usuario_id' => null,
            ]);
    }

    public function down(): void
    {
        // Confirmation details cannot be reconstructed for tickets that were pending.
    }
};
