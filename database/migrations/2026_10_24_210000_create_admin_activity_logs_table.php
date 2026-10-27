<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->increments('id_registro');
            $table->integer('id_usuario')->index();
            $table->string('nombre_usuario', 50)->nullable();
            $table->string('rol_usuario', 50);
            $table->string('accion', 40);
            $table->dateTime('momento')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
