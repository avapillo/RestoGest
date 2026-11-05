<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registro_gasto', function (Blueprint $table) {
            $table->integer('fk_id_historial')->nullable();
            $table->foreign('fk_id_historial', 'fk_registro_gasto_historial')
                ->references('id_historial')
                ->on('historial_caja')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_gasto', function (Blueprint $table) {
            $table->dropForeign('fk_registro_gasto_historial');
            $table->dropColumn('fk_id_historial');
        });
    }
};
