<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\MesasController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\SeccionController;
// Login

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

Route::view('/mantenimiento', 'mantenimiento')->name('mantenimiento');

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


// Rutas de mesas
Route::get('/Mesas', [MesasController::class, 'mostrarMesas'])->name('mesas.index');

// Rutas de control de usuarios
Route::get('/Usuarios', [UsuariosController::class, 'mostrarUsuarios'])->name('usuario.index');
Route::post('/Usuarios/guardar', [UsuariosController::class, 'nuevoUsuario'])->name('usuario.store');
Route::post('/Usuarios/modificar', [UsuariosController::class, 'modificarUsuario'])->name('usuario.update');
Route::delete('/Usuarios/{id}/eliminar', [UsuariosController::class, 'eliminarUsuario'])->name('usuario.destroy');

Route::post('/Mesas/pagar', [MesasController::class, 'procesarPago'])->name('pedido.pagar');

Route::post('/Mesas/Pedido', [MesasController::class, 'realizarPedido'])->name('cargar.store');

Route::post('/Mesas/Pedido/{id_pedido}/modificar', [MesasController::class, 'modificarPedido'])->name('pedido.update');

Route::get('/pedidos/crear/{mesa}', [PedidoController::class, 'create'])->name('pedidos.create');


// Ruta de ventas
