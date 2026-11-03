<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('estados')->insertOrIgnore([
            ['id' => 1, 'estado' => 'Recibido'],
            ['id' => 2, 'estado' => 'Preparando'],
            ['id' => 3, 'estado' => 'Listo'],
        ]);
    }

    public function down(): void
    {
        DB::table('estados')
            ->whereIn('id', [1, 2, 3])
            ->whereIn('estado', ['Recibido', 'Preparando', 'Listo'])
            ->whereNotIn('id', DB::table('pedidos')->select('fk_id_estado'))
            ->delete();
    }
};
