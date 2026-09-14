<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
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
Route::get('/Mesas', [MesasController::class, 'mostrarMesas'])->name('intefaz_mesa');
Route::post('/Mesas/pagar', [MesasController::class, 'procesarPago'])->name('pedidos.pagar');
Route::put('/Mesas/actualizar', [MesasController::class, 'actualizarPedido'])->name('pedidos.actualizar');


// Registro de nuevo usuario
Route::get('/Usuario', [UsuariosController::class, 'mostrarUsuarios'])->name('usuario.index');
Route::post('/Usuario/guardar', [UsuariosController::class, 'nuevoUsuario'])->name('usuario.store');
Route::post('/Usuario/modificar', [UsuariosController::class, 'modificarUsuario'])->name('usuario.update');
Route::delete('/Usuario/{id}/eliminar', [UsuariosController::class, 'eliminarUsuario'])->name('usuario.destroy');

// Ruta pedidos para llevar
Route::get('/Admin/Pedidos', [SeccionController::class, 'obtenerSeecion'])->name('muestra_pedido');
