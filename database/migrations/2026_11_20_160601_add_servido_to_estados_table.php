
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use RuntimeException;

return new class extends Migration
{
    public function up(): void
    {
        $estado = DB::table('estados')
            ->where('id', 4)
            ->first();

        if ($estado) {
            if (mb_strtolower(trim($estado->estado ?? '')) !== 'servido') {
                throw new RuntimeException(
                    'El ID 4 ya esta ocupado por otro estado.'
                );
            }

            return;
        }

        $servido = DB::table('estados')
            ->whereRaw('LOWER(TRIM(estado)) = ?', ['servido'])
            ->first();

        if ($servido) {
            throw new RuntimeException(
                'El estado Servido ya existe con un ID diferente de 4.'
            );
        }

        DB::table('estados')->insert([
            'id' => 4,
            'estado' => 'Servido',
        ]);
    }

    public function down(): void
    {
        // No se elimina Servido para proteger registros existentes.
    }
};