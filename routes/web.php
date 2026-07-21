<?php

use Illuminate\Support\Facades\Route;

// Ruta principal que apunta a home.blade.php
Route::get('/', function () {
    return view('home');
})->name('home');

// Rutas nombradas para que los botones de la barra lateral no den error
Route::get('/comandas', function () {
    return view('home');
})->name('comandas.index');

Route::get('/mesas', function () {
    return view('home');
})->name('mesas.index');

Route::get('/cocina', function () {
    return view('home');
})->name('cocina.index');

Route::get('/reportes', function () {
    return view('home');
})->name('reportes.index');

Route::get('/historial', function () {
    return view('home');
})->name('historial.index');
