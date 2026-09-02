<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MesasController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminPedidoController;

// Mostrar formulario
Route::get('/', [LoginController::class, 'mostrarLogin'])->name('login');

// Recibir formulario por POST
Route::post('/login', [LoginController::class, 'validarUsuario'])->name('login.post');

// Cerrar sesión
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');


// Ruta principal que apunta a home.blade.php
Route::get('/Inicio', function () {
    return view('home');
})->name('home');

// Producto para llevar
Route::get('/ProductoLlevar', function (){
    return view('interfaz_paraLlevar');
})->name('interfaz_paraLlevar');

// Ruta para mostrar el formulario de registro de productos
Route::get('/Producto', [ProductoController::class, 'mostrarProducto'])->name('producto.index');
// Llama al metodo de registro producto en ProductoController.php
Route::post('/Producto/guardar', [ProductoController::class, 'registroProducto'])->name('producto.store');

Route::post('/Producto/modificar', [ProductoController::class, 'modificarProducto'])->name('producto.update');

Route::delete('/Producto/{id}/eliminar', [ProductoController::class, 'eliminarProducto'])->name('producto.destroy');

Route::post('/Categoria/guardar', [ProductoController::class, 'registroCategoria'])->name('categoria.store');


// Ruta pedidos para llevar
// Route::get('/admin/pedidos', [AdminPedidoController::class, 'pedido'])->name('muestra_pedido');


// Rutas de mesas
Route::get('/Mesas', [MesasController::class, 'mostrarMesas'])->name('mesas.index');

// Rutas de Pedido
// Route::get('/admin/pedidos', [AdminPedidoController::class, 'pedido'])->name('muestra_pedido');
// Route::post('/admin/pedidos/pagar', [AdminPedidoController::class, 'procesarPago'])->name('pedidos.pagar');
