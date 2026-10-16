<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HistorialCajaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ApiProductoController;
use App\Http\Controllers\ApiCocinaController;

// =============================================
// API DEL HISTORIAL DE CAJA
// =============================================
Route::get('/historial-caja', [
    HistorialCajaController::class,
    'index'
]);

// =============================================
// REGISTRO DE PRODUCTOS
// =============================================
Route::post('/registro-producto', [
    ProductoController::class,
    'registroProducto'
]);

// =============================================
// API DE PRODUCTOS PARA LA APP
// =============================================

// Obtener todos los productos
Route::get('/v1/productos', [
    ApiProductoController::class,
    'obtenerTodoLosProductos'
]);

// Obtener productos por categoría
Route::get('/v1/productos/categoria/{id}', [
    ApiProductoController::class,
    'obtenerProductoPorCategoria'
]);

// =============================================
// API DE PEDIDOS PARA LA COCINA
// =============================================
Route::get('/cocina/pedidos', [
    ApiCocinaController::class,
    'pedidos'
]);