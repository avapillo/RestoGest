<?php
use App\Http\Controllers\ApiCocinaController;
use App\Http\Controllers\VentasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HistorialCajaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ApiProductoController;
use App\Http\Controllers\MesasController;
use App\Http\Controllers\LoginController;
// La caja requiere una sesión web válida de administrador.
Route::middleware('web')->group(function () {
    Route::get('/historial-caja', [HistorialCajaController::class, 'index']);
    Route::get('/caja/estado', [HistorialCajaController::class, 'estado']);
    Route::get('/ventas', [VentasController::class, 'buscar']);
    Route::get('/productos-mas-vendidos', [VentasController::class, 'productosMasVendidos']);

    Route::post('/caja/abrir', [HistorialCajaController::class, 'abrir']);

    Route::post('/caja/pago', [HistorialCajaController::class, 'registrarPago']);

    Route::post('/caja/cerrar', [HistorialCajaController::class, 'store']);
});
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

// URL 2: Obtener productos filtrados por una categoría específica
Route::get('/v1/productos/categoria/{id}', [ApiProductoController::class, 'obtenerProductoPorCategoria']);
// Pedidos para la aplicación de cocina
Route::get('/cocina/pedidos', [ApiCocinaController::class, 'pedidos']);
Route::post('/cocina/pedidos', [ApiCocinaController::class, 'guardar']);
Route::patch('/cocina/pedidos/{id}/estado', [
    ApiCocinaController::class,
    'actualizarEstado'
]);
Route::get('/mesas', [MesasController::class, 'listarApi']);
Route::post('/login-app', [LoginController::class, 'loginApp']);
Route::get('/mesas/{idMesa}/pedidos-pendientes', [
    MesasController::class,
    'pedidosPendientesApi'
]);
// =============================================
// IMÁGENES DE PRODUCTOS PARA LAS APPS FLUTTER
// =============================================

Route::get('/imagen-producto/{ruta}', function (string $ruta) {

    $ruta = str_replace('\\', '/', $ruta);
    $nombreArchivo = basename($ruta);

    // Solo permite imágenes guardadas en productos
    if (
        !str_starts_with($ruta, 'productos/') ||
        $nombreArchivo === '' ||
        $nombreArchivo === '.' ||
        $nombreArchivo === '..'
    ) {
        abort(404);
    }

    $archivo = 'productos/' . $nombreArchivo;

    $disco = Storage::disk('public');

    if (!$disco->exists($archivo)) {
        abort(404);
    }

    return response()->file($disco->path($archivo));

})->where('ruta', '.*');
