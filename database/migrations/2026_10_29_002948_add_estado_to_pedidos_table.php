
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->integer('fk_id_estado')
                ->nullable()
                ->after('fk_id_tipo_pago');
        });

        DB::table('pedidos')
            ->whereNull('fk_id_estado')
            ->update(['fk_id_estado' => 1]);

        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign('fk_id_estado', 'fk_pedidos_estado')
                ->references('id')
                ->on('estados');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign('fk_pedidos_estado');
            $table->dropColumn('fk_id_estado');
        });
    }
};
