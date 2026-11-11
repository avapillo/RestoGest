<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_caja', function (Blueprint $table) {
            $table->decimal('monto_cierre', 12, 2)->default(0);
        });

        DB::table('historial_caja')
            ->orderBy('id_historial')
            ->get(['id_historial', 'monto_total', 'monto_inicial'])
            ->each(function (object $cierre): void {
                $montoGastos = (float) DB::table('registro_gasto')
                    ->where('fk_id_historial', $cierre->id_historial)
                    ->sum('monto');
                $montoCierreCentavos = (int) round((float) $cierre->monto_inicial * 100)
                    + (int) round((float) $cierre->monto_total * 100)
                    - (int) round($montoGastos * 100);

                DB::table('historial_caja')
                    ->where('id_historial', $cierre->id_historial)
                    ->update(['monto_cierre' => $montoCierreCentavos / 100]);
            });
    }

    public function down(): void
    {
        Schema::table('historial_caja', function (Blueprint $table) {
            $table->dropColumn('monto_cierre');
        });
    }
};
