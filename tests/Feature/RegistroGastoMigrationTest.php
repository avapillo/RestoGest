<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

afterEach(function () {
    Schema::dropIfExists('registro_gasto');
    Schema::dropIfExists('historial_caja');
});

test('creates the original expense schema when it is missing and lets later migrations extend it', function () {
    $createMigration = require database_path('migrations/2026_11_04_000000_create_registro_gasto_table.php');
    $createMigration->up();

    expect(Schema::getColumnListing('registro_gasto'))
        ->toBe(['id_gasto', 'producto', 'monto']);
    expect(Schema::getColumnType('registro_gasto', 'id_gasto'))->toBe('integer');
    expect(in_array(Schema::getColumnType('registro_gasto', 'monto'), ['decimal', 'numeric'], true))
        ->toBeTrue();

    Schema::create('historial_caja', function (Blueprint $table) {
        $table->increments('id_historial');
    });

    $historyMigration = require database_path('migrations/2026_11_05_132453_add_historial_caja_to_registro_gasto_table.php');
    $historyMigration->up();

    expect(Schema::hasColumn('registro_gasto', 'fk_id_historial'))->toBeTrue();

    $identityMigration = require database_path('migrations/2026_11_11_000001_fix_auto_increment_on_registro_gasto.php');
    $identityMigration->up();

    expect(Schema::hasColumn('registro_gasto', 'id_gasto'))->toBeTrue();
    expect(DB::table('registro_gasto')->insertGetId([
        'producto' => 'Prueba',
        'monto' => 100,
    ], 'id_gasto'))->toBe(1);
});

test('does not recreate the expense table when an earlier migration already created it', function () {
    Schema::create('registro_gasto', function (Blueprint $table) {
        $table->increments('id_gasto');
        $table->string('producto')->nullable();
        $table->decimal('monto')->nullable();
    });

    $migration = require database_path('migrations/2026_11_04_000000_create_registro_gasto_table.php');
    $migration->up();

    expect(Schema::getColumnListing('registro_gasto'))
        ->toBe(['id_gasto', 'producto', 'monto']);
});
