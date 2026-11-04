<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->integer('fk_id_usuario')->nullable();
            $table->string('nombre_creador', 50)->nullable();
            $table->string('rol_creador', 50)->nullable();

            $table->foreign('fk_id_usuario')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['fk_id_usuario']);
            $table->dropColumn(['fk_id_usuario', 'nombre_creador', 'rol_creador']);
        });
    }
};
