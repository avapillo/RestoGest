<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_estado', function (Blueprint $table) {
            $table->decimal('monto_inicial', 12, 2)->default(0);
        });

        Schema::table('historial_caja', function (Blueprint $table) {
            $table->decimal('monto_inicial', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('historial_caja', function (Blueprint $table) {
            $table->dropColumn('monto_inicial');
        });

        Schema::table('caja_estado', function (Blueprint $table) {
            $table->dropColumn('monto_inicial');
        });
    }
};
