<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 1. IMPORTANTE: Importa tu ProductoController aquí arriba junto al de Caja
use App\Http\Controllers\HistorialCajaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ApiProductoController;

// Ruta que ya tenías
Route::get('/historial-caja', [HistorialCajaController::class, 'index']);
Route::get('/caja/estado', [HistorialCajaController::class, 'estado']);

Route::post('/caja/abrir', [HistorialCajaController::class, 'abrir']);

Route::post('/caja/pago', [HistorialCajaController::class, 'registrarPago']);

Route::post('/caja/cerrar', [HistorialCajaController::class, 'store']);
/*
Route::get('/historial-caja', function () {
    // Retornamos un arreglo ficticio para probar que el HTML y JS dibujen la tabla
    return response()->json([
        [
            'momento_cierre' => '2026-08-19 21:00:00',
            'monto_total' => 150000
        ],
        [
            'momento_cierre' => '2026-08-18 20:30:00',
            'monto_total' => 98500
        ]
    ]);
});
*/
// 2. AGREGA ESTA RUTA: Es la que procesará el formulario con JavaScript (POST)
Route::post('/registro-producto', [ProductoController::class, 'registroProducto']);


// API para la App

// URL 1: Obtener la lista completa de productos
Route::get('/v1/productos', [ApiProductoController::class, 'obtenerTodoLosProductos']);

// URL 2: Obtener productos filtrados por una categoría específica
Route::get('/v1/productos/categoria/{id}', [ApiProductoController::class, 'obtenerProductoPorCategoria']);
