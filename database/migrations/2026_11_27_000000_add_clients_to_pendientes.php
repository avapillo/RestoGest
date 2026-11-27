<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('dni_ultimos_4', 4)->nullable();
            $table->timestamps();

            $table->index('nombre');
            $table->index('dni_ultimos_4');
        });

        Schema::table('pendientes', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->restrictOnDelete();
            $table->dateTime('fecha_registro')->nullable();
        });

        DB::table('pendientes')->orderBy('id')->each(function (object $pendiente): void {
            $fecha = $pendiente->created_at ?? now();
            $clienteId = DB::table('clientes')->insertGetId([
                'nombre' => trim($pendiente->nombre),
                'dni_ultimos_4' => null,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            DB::table('pendientes')
                ->where('id', $pendiente->id)
                ->update([
                    'cliente_id' => $clienteId,
                    'fecha_registro' => $fecha,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('pendientes', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn(['cliente_id', 'fecha_registro']);
        });

        Schema::dropIfExists('clientes');
    }
};
