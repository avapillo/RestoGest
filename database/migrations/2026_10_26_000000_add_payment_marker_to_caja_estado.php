<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_estado', function (Blueprint $table) {
            $table->integer('id_registro_inicio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('caja_estado', function (Blueprint $table) {
            $table->dropColumn('id_registro_inicio');
        });
    }
};
