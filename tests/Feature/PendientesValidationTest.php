<?php

use App\Http\Middleware\AdminSession;

test('no permite numeros ni simbolos al crear un pendiente', function () {
    $this->withoutMiddleware(AdminSession::class)
        ->from('/Pendientes')
        ->post('/Pendientes/guardar', [
            'nombre' => 'Juan2',
            'monto' => 100,
        ])
        ->assertSessionHasErrors('nombre');
});

test('no permite numeros ni simbolos al editar un pendiente', function () {
    $this->withoutMiddleware(AdminSession::class)
        ->from('/Pendientes')
        ->put('/Pendientes/1/modificar', [
            'nombre' => 'Ana!',
            'monto' => 100,
        ])
        ->assertSessionHasErrors('nombre');
});
