<?php

use App\Http\Middleware\AdminSession;
use App\Models\Cliente;
use App\Models\Pendiente;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('crea cliente sin DNI y vincula el movimiento por ID', function () {
    $this->withoutMiddleware(AdminSession::class)
        ->post(route('pendientes.store'), [
            'nombre' => '  Ana Pérez  ',
            'monto' => 1250,
            'dni_ultimos_4' => '0042',
        ])
        ->assertRedirect(route('pendientes.index'));

    $cliente = Cliente::query()->sole();

    expect($cliente->nombre)->toBe('Ana Pérez')
        ->and($cliente->dni_ultimos_4)->toBeNull();

    $this->assertDatabaseHas('pendientes', [
        'cliente_id' => $cliente->id,
        'nombre' => 'Ana Pérez',
        'monto' => 1250,
    ]);
});

test('permite elegir el cliente existente o crear un homónimo con DNI', function () {
    $existente = Cliente::create([
        'nombre' => 'Juan Pérez',
        'dni_ultimos_4' => null,
    ]);
    Pendiente::create([
        'nombre' => $existente->nombre,
        'cliente_id' => $existente->id,
        'monto' => 100,
        'fecha_registro' => now()->subDay(),
    ]);

    $this->withoutMiddleware(AdminSession::class)
        ->getJson(route('pendientes.buscar', ['nombre' => 'Juan Pérez']))
        ->assertOk()
        ->assertJsonPath('coincidencias.0.id', $existente->id)
        ->assertJsonPath('coincidencias.0.ultimo_registro.monto', '100.00');

    $this->post(route('pendientes.store'), [
        'nombre' => 'Juan Pérez',
        'cliente_id' => $existente->id,
        'monto' => 250,
    ])->assertRedirect(route('pendientes.index'));

    $this->post(route('pendientes.store'), [
        'nombre' => 'Juan Pérez',
        'dni_ultimos_4' => '0042',
        'monto' => 300,
    ])->assertRedirect(route('pendientes.index'));

    expect(Cliente::where('nombre', 'Juan Pérez')->count())->toBe(2);
    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Juan Pérez',
        'dni_ultimos_4' => '0042',
    ]);
    $this->assertDatabaseHas('pendientes', [
        'cliente_id' => $existente->id,
        'monto' => 250,
    ]);
});

test('busca por nombre parcial o DNI exacto y ordena por fecha de movimiento', function () {
    $anterior = Cliente::create([
        'nombre' => 'María López',
        'dni_ultimos_4' => '0012',
    ]);
    $reciente = Cliente::create([
        'nombre' => 'María López',
        'dni_ultimos_4' => '9999',
    ]);

    Pendiente::create([
        'nombre' => $anterior->nombre,
        'cliente_id' => $anterior->id,
        'monto' => 100,
        'fecha_registro' => now()->subDay(),
    ]);
    Pendiente::create([
        'nombre' => $reciente->nombre,
        'cliente_id' => $reciente->id,
        'monto' => 200,
        'fecha_registro' => now(),
    ]);

    $this->withoutMiddleware(AdminSession::class)
        ->getJson(route('pendientes.buscar', ['q' => '0012']))
        ->assertOk()
        ->assertJsonCount(1, 'resultados')
        ->assertJsonPath('resultados.0.dni_ultimos_4', '0012');

    $this->getJson(route('pendientes.buscar', ['q' => 'María']))
        ->assertOk()
        ->assertJsonCount(2, 'resultados')
        ->assertJsonPath('resultados.0.monto', '200.00')
        ->assertJsonPath('resultados.1.monto', '100.00');
});
