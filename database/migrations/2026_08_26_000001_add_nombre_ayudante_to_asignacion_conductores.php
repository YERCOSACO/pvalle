<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignacion_conductores', function (Blueprint $table) {
            $table->foreignId('conductor_id')->nullable()->change();
            $table->string('nombre_ayudante')->nullable()->after('tipo_asignacion');
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_conductores', function (Blueprint $table) {
            $table->dropColumn('nombre_ayudante');
            $table->foreignId('conductor_id')->nullable(false)->change();
        });
    }
};