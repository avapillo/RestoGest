<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registro_pago', function (Blueprint $table) {
            $table->integer('fk_id_pedido')->nullable()->unique()->after('fk_tipo_pago');
            $table->foreign('fk_id_pedido')
                ->references('id_pedido')
                ->on('pedidos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registro_pago', function (Blueprint $table) {
            $table->dropForeign(['fk_id_pedido']);
            $table->dropUnique(['fk_id_pedido']);
            $table->dropColumn('fk_id_pedido');
        });
    }
};
