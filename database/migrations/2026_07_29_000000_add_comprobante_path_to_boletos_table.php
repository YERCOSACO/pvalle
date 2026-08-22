<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->string('comprobante_path')->nullable()->after('expira_en');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropColumn('comprobante_path');
        });
    }
};
