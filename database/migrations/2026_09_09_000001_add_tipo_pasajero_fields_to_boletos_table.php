<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->boolean('espacio_extra')->default(false)->after('telefono_pasajero');
            $table->boolean('mascota')->default(false)->after('espacio_extra');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropColumn(['espacio_extra', 'mascota']);
        });
    }
};
