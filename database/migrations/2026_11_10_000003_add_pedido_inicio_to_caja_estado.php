<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_estado', function (Blueprint $table) {
            $table->integer('id_pedido_inicio')->nullable();
        });

        $ultimoPedido = DB::table('pedidos')->max('id_pedido') ?? 0;

        DB::table('caja_estado')
            ->where('abierta', true)
            ->update(['id_pedido_inicio' => $ultimoPedido]);
    }

    public function down(): void
    {
        Schema::table('caja_estado', function (Blueprint $table) {
            $table->dropColumn('id_pedido_inicio');
        });
    }
};
