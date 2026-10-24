<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('productos');
    Schema::dropIfExists('categoria');
    Schema::dropIfExists('usuario_roles');
    Schema::dropIfExists('usuarios');
    Schema::dropIfExists('rol_usuario');

    Schema::create('usuarios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('gmail')->unique();
        $table->string('contrasenia');
    });

    Schema::create('rol_usuario', function (Blueprint $table) {
        $table->integer('id_rol')->primary();
        $table->string('rol_nombre');
    });

    Schema::create('usuario_roles', function (Blueprint $table) {
        $table->integer('fk_id_usuario');
        $table->integer('fk_id_rol');
        $table->primary(['fk_id_usuario', 'fk_id_rol']);
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

test('un usuario válido puede iniciar sesión con Gmail y contraseña', function () {
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => Hash::make('2580-segura'),
    ]);

    $response = $this->post('/login', [
        'gmail' => 'milton@gmail.com',
        'contrasenia' => '2580-segura',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertTrue(session()->has('usuario_id'));
    $this->assertTrue(session()->has('usuario_nom'));
    $this->assertSame('Milton', session('usuario_nom'));
});

test('un usuario antiguo puede iniciar sesión y actualizar su contraseña a hash', function () {
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => '2580',
    ]);

    $this->post('/login', [
        'gmail' => 'milton@gmail.com',
        'contrasenia' => '2580',
    ])->assertRedirect(route('home'));

    $this->assertSame('Milton', session('usuario_nom'));
    $this->assertTrue(Hash::check('2580', DB::table('usuarios')->value('contrasenia')));
});

test('las credenciales inválidas no permiten iniciar sesión', function () {
    $response = $this->from('/')->post('/login', [
        'gmail' => 'milton@gmail.com',
        'contrasenia' => 'incorrecta',
    ]);

    $response->assertRedirect();
    $this->assertSame('Usuario o contraseña incorrectos.', session('error'));
});

test('se puede registrar un usuario con Gmail único y contraseña segura', function () {
    DB::table('rol_usuario')->insert(['id_rol' => 1, 'rol_nombre' => 'Administrador']);

    $this->post('/Usuarios/guardar', [
        'nombre' => 'Milton',
        'gmail' => 'Milton@Gmail.com',
        'contrasenia' => 'clave-segura',
        'contrasenia_confirmation' => 'clave-segura',
        'roles' => [1],
    ])->assertRedirect(route('usuario.index'));

    $this->assertDatabaseHas('usuarios', [
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
    ]);
    $this->assertTrue(Hash::check('clave-segura', DB::table('usuarios')->value('contrasenia')));
});

test('no se puede registrar un Gmail repetido ni contraseñas distintas', function () {
    DB::table('rol_usuario')->insert(['id_rol' => 1, 'rol_nombre' => 'Administrador']);
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => Hash::make('clave-segura'),
    ]);

    $this->from('/Usuarios')->post('/Usuarios/guardar', [
        'nombre' => 'Otro usuario',
        'gmail' => 'MILTON@GMAIL.COM',
        'contrasenia' => 'clave-segura',
        'contrasenia_confirmation' => 'clave-segura',
        'roles' => [1],
    ])->assertSessionHasErrors('gmail');

    $this->from('/Usuarios')->post('/Usuarios/guardar', [
        'nombre' => 'Otro usuario',
        'gmail' => 'otro@gmail.com',
        'contrasenia' => 'clave-segura',
        'contrasenia_confirmation' => 'otra-clave',
        'roles' => [1],
    ])->assertSessionHasErrors('contrasenia');
});

test('la contraseña se puede cambiar desde el formulario de edición', function () {
    $passwordId = DB::table('usuarios')->insertGetId([
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => Hash::make('clave-anterior'),
    ]);

    $this->post('/Usuarios/modificar', [
        'id' => $passwordId,
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => 'clave-nueva',
    ])->assertRedirect(route('usuario.index'));

    $this->assertTrue(Hash::check('clave-nueva', DB::table('usuarios')->where('id', $passwordId)->value('contrasenia')));
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
