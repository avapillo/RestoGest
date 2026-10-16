<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('productos');
    Schema::dropIfExists('categoria');
    Schema::dropIfExists('usuarios');

    Schema::create('usuarios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('gmail')->nullable();
        $table->string('contrasenia');
    });

    Schema::create('categoria', function (Blueprint $table) {
        $table->id();
        $table->string('categoria');
    });

    Schema::create('productos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->integer('precio');
        $table->string('imagen')->nullable();
        $table->unsignedBigInteger('fk_id_categoria');
        $table->foreign('fk_id_categoria')->references('id')->on('categoria');
    });
});

test('la página de login se carga correctamente', function () {
    $this->get('/')->assertOk()->assertViewIs('interfaz_login');
});

test('un usuario válido puede iniciar sesión y ser redirigido al inicio', function () {
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'contrasenia' => 2580,
    ]);

    $response = $this->post('/login', [
        'nombre' => 'Milton',
        'contrasenia' => 2580,
    ]);

    $response->assertRedirect(route('home'));
    $this->assertTrue(session()->has('usuario_id'));
    $this->assertTrue(session()->has('usuario_nom'));
    $this->assertSame('Milton', session('usuario_nom'));
});

test('un usuario puede iniciar sesión con su Gmail', function () {
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'gmail' => 'milton@example.com',
        'contrasenia' => '2580',
    ]);

    $this->post('/login', [
        'nombre' => 'Milton',
        'gmail' => 'milton@example.com',
    ])->assertRedirect(route('home'));

    $this->assertSame('Milton', session('usuario_nom'));
});

test('las credenciales inválidas no permiten iniciar sesión', function () {
    $response = $this->from('/')->post('/login', [
        'nombre' => 'Milton',
        'contrasenia' => 9999,
    ]);

    $response->assertRedirect();
    $this->assertSame('Usuario o contraseña incorrectos.', session('error'));
});

test('la vista de productos muestra categorías y productos registrados', function () {
    $categoriaId = DB::table('categoria')->insertGetId([
        'categoria' => 'Bebidas',
    ]);

    DB::table('productos')->insert([
        'nombre' => 'Café',
        'precio' => 2500,
        'imagen' => null,
        'fk_id_categoria' => $categoriaId,
    ]);

    $this->get('/Producto')
        ->assertOk()
        ->assertViewHas('categorias')
        ->assertViewHas('productos');
});

test('se puede registrar un producto desde la interfaz', function () {
    $categoriaId = DB::table('categoria')->insertGetId([
        'categoria' => 'Platos',
    ]);

    $this->post('/Producto/guardar', [
        'nombre' => 'Milanesa',
        'precio' => 18000,
        'fk_id_categoria' => $categoriaId,
    ])->assertRedirect(route('producto.index'));

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Milanesa',
        'precio' => 18000,
        'fk_id_categoria' => $categoriaId,
    ]);
});
