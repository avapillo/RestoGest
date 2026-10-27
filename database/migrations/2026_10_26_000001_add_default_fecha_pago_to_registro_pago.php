<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registro_pago', function (Blueprint $table) {
            $table->date('fecha_pago')
                ->nullable()
                ->default(DB::raw('(CURRENT_DATE)'))
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('registro_pago', function (Blueprint $table) {
            $table->date('fecha_pago')->nullable()->default(null)->change();
        });
    }
};
