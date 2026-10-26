<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\MesasController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\ComboController;
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
Route::get('/Categoria/listar', [CategoriaController::class, 'mostrarCategoria'])->name('categoria.index');
Route::post('/Categoria/guardar', [CategoriaController::class, 'registroCategoria'])->name('categoria.store');
Route::post('/Categoria/modificar', [CategoriaController::class, 'modificarCategoria'])->name('categoria.update');
Route::delete('/Categoria/{id}/eliminar', [CategoriaController::class, 'eliminarCategoria'])->name('categoria.destroy');


// Rutas de mesas
Route::get('/Mesas', [MesasController::class, 'mostrarMesas'])->name('mesas.index');
Route::post('/Mesas/guardar', [MesasController::class, 'registrarMesa'])->name('mesas.store');
Route::get('/Secciones/listar', [SeccionController::class, 'mostrarSecciones'])->name('seccion.index');
Route::post('/Secciones/guardar', [SeccionController::class, 'registrarSeccion'])->name('seccion.store');
Route::post('/Secciones/modificar', [SeccionController::class, 'modificarSeccion'])->name('seccion.update');
Route::delete('/Secciones/{id}/eliminar', [SeccionController::class, 'eliminarSeccion'])->name('seccion.destroy');
Route::get('/Combos/listar', [ComboController::class, 'mostrarCombos'])->name('combo.index');
Route::post('/Combos/guardar', [ComboController::class, 'registrarCombo'])->name('combo.store');
Route::post('/Combos/modificar', [ComboController::class, 'modificarCombo'])->name('combo.update');
Route::delete('/Combos/{id}/eliminar', [ComboController::class, 'eliminarCombo'])->name('combo.destroy');

// Rutas de control de usuarios
Route::get('/Usuarios', [UsuariosController::class, 'mostrarUsuarios'])->name('usuario.index');
Route::post('/Usuarios/guardar', [UsuariosController::class, 'nuevoUsuario'])->name('usuario.store');
Route::post('/Usuarios/modificar', [UsuariosController::class, 'modificarUsuario'])->name('usuario.update');
Route::delete('/Usuarios/{id}/eliminar', [UsuariosController::class, 'eliminarUsuario'])->name('usuario.destroy');
Route::get('/Roles/listar', [UsuariosController::class, 'mostrarRoles'])->name('rol.index');
Route::post('/Roles/guardar', [UsuariosController::class, 'nuevoRol'])->name('rol.store');
Route::post('/Roles/modificar', [UsuariosController::class, 'modificarRol'])->name('rol.update');
Route::delete('/Roles/{id}/eliminar', [UsuariosController::class, 'eliminarRol'])->name('rol.destroy');

Route::post('/Mesas/pagar', [MesasController::class, 'procesarPago'])->name('pedido.pagar');

Route::post('/Mesas/Pedido', [MesasController::class, 'realizarPedido'])->name('cargar.store');

Route::post('/Mesas/Pedido/{id_pedido}/modificar', [MesasController::class, 'modificarPedido'])->name('pedido.update');

Route::get('/pedidos/crear/{mesa}', [PedidoController::class, 'create'])->name('pedidos.create');


// Ruta de ventas
Route::get('/Ventas', [VentasController::class, 'index'])->name('ventas.index');
