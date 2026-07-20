<?php

use Illuminate\Support\Facades\Route;
// Rutas
Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('login');
});
