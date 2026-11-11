<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registro_gasto', function (Blueprint $table) {
            $table->integer('id_gasto')->autoIncrement()->change();
        });
    }

    public function down(): void
    {
        Schema::table('registro_gasto', function (Blueprint $table) {
            $table->integer('id_gasto')->change();
        });
    }
};
