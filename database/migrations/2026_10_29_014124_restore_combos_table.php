
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('combos')) {
            Schema::create('combos', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->string('combo', 50)->nullable();
            });
        }

        if (
            Schema::hasTable('detalle_pedido') &&
            Schema::hasColumn('detalle_pedido', 'fk_id_combo')
        ) {
            $foreignKeys = collect(
                Schema::getForeignKeys('detalle_pedido')
            );

            $existeRelacion = $foreignKeys->contains(
                fn ($fk) => $fk['name'] === 'fk_detalle_combo'
            );

            if (!$existeRelacion) {
                Schema::table('detalle_pedido', function (Blueprint $table) {
                    $table->foreign('fk_id_combo', 'fk_detalle_combo')
                        ->references('id')
                        ->on('combos');
                });
            }
        }
    }

    public function down(): void
    {
        // No eliminar combos ni su relación automáticamente:
        // podría haber datos que deban conservarse.
    }
};
