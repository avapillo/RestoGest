<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_estado', function (Blueprint $table) {
            $table->id();

            $table->boolean('abierta')->default(false);

            $table->decimal('monto_actual', 12, 2)->default(0);

            $table->dateTime('momento_apertura')->nullable();

            $table->unsignedBigInteger('id_usuario')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_estado');
    }
};