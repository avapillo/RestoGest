<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registro_gasto')) {
            return;
        }

        Schema::create('registro_gasto', function (Blueprint $table) {
            $table->increments('id_gasto');
            $table->string('producto')->nullable();
            $table->decimal('monto')->nullable();
        });
    }

    public function down(): void
    {
        // This migration may only be repairing a table owned by an earlier migration.
    }
};
