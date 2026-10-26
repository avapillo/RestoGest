<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('admin_activity_logs');
    Schema::dropIfExists('historial_caja');
    Schema::dropIfExists('caja_estado');
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
        $table->integer('id_rol_usuario')->nullable();
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

    Schema::create('caja_estado', function (Blueprint $table) {
        $table->id();
        $table->boolean('abierta')->default(false);
        $table->decimal('monto_actual', 12, 2)->default(0);
        $table->dateTime('momento_apertura')->nullable();
        $table->integer('id_usuario')->nullable();
        $table->timestamps();
    });

    Schema::create('historial_caja', function (Blueprint $table) {
        $table->increments('id_historial');
        $table->decimal('monto_total', 10, 2)->nullable();
        $table->dateTime('momento_cierre')->nullable();
        $table->integer('id_usuario')->nullable();
    });

    Schema::create('tipo_pago', function (Blueprint $table) {
        $table->increments('id');
        $table->string('tipo_pago');
    });

    Schema::create('pedidos', function (Blueprint $table) {
        $table->increments('id_pedido');
        $table->decimal('monto_total', 10, 2)->nullable();
    });

    Schema::create('detalle_pedido', function (Blueprint $table) {
        $table->increments('id_detalle');
        $table->integer('fk_id_pedido');
        $table->decimal('subtotal', 10, 2)->nullable();
    });

    Schema::create('registro_pago', function (Blueprint $table) {
        $table->increments('id_registro');
        $table->decimal('monto', 10, 2)->nullable();
        $table->integer('fk_tipo_pago')->nullable();
        $table->date('fecha_pago')->nullable();
        $table->integer('fk_id_historial')->nullable();
        $table->integer('fk_id_pedido')->nullable();
    });

    Schema::create('admin_activity_logs', function (Blueprint $table) {
        $table->increments('id_registro');
        $table->integer('id_usuario');
        $table->string('nombre_usuario')->nullable();
        $table->string('rol_usuario');
        $table->string('accion');
        $table->dateTime('momento');
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

    DB::table('rol_usuario')->insert(['id_rol' => 1, 'rol_nombre' => 'Administrador']);
    $adminId = DB::table('usuarios')->insertGetId([
        'nombre' => 'Admin de prueba',
        'gmail' => 'admin-pruebas@gmail.com',
        'contrasenia' => Hash::make('clave-segura'),
        'id_rol_usuario' => 1,
    ]);
    $this->withSession(['usuario_id' => $adminId, 'usuario_nom' => 'Admin de prueba']);
});

test('la página de login se carga correctamente', function () {
    $this->get('/')->assertOk()->assertViewIs('interfaz_login');
});

test('un usuario válido puede iniciar sesión con Gmail y contraseña', function () {
    DB::table('usuarios')->insert([
        'nombre' => 'Milton',
        'gmail' => 'milton@gmail.com',
        'contrasenia' => Hash::make('2580-segura'),
        'id_rol_usuario' => 1,
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
        'id_rol_usuario' => 1,
    ]);

    $this->post('/login', [
        'gmail' => 'milton@gmail.com',
        'contrasenia' => '2580',
    ])->assertRedirect(route('home'));

    $this->assertSame('Milton', session('usuario_nom'));
    $this->assertTrue(Hash::check(
        '2580',
        DB::table('usuarios')->where('gmail', 'milton@gmail.com')->value('contrasenia')
    ));
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

test('el cierre guarda el total y muestra quien cerró solo a administradores', function () {
    $adminId = DB::table('usuarios')->insertGetId([
        'nombre' => 'Admin',
        'gmail' => 'admin@gmail.com',
        'contrasenia' => Hash::make('clave-segura'),
        'id_rol_usuario' => 1,
    ]);
    $cajeroId = DB::table('usuarios')->insertGetId([
        'nombre' => 'Cajero',
        'gmail' => 'cajero@gmail.com',
        'contrasenia' => Hash::make('clave-segura'),
    ]);
    DB::table('caja_estado')->insert([
        'abierta' => true,
        'monto_actual' => 7250.50,
        'id_usuario' => $adminId,
    ]);

    $this->withSession(['usuario_id' => $adminId])
        ->getJson('/api/caja/estado')
        ->assertOk()
        ->assertJsonPath('abierta', true)
        ->assertJsonPath('monto_actual', 7250.5);

    $this->postJson('/api/caja/cerrar')
        ->assertCreated()
        ->assertJsonPath('abierta', false);

    $this->assertDatabaseHas('historial_caja', [
        'monto_total' => 7250.50,
        'id_usuario' => $adminId,
    ]);

    $this->getJson('/api/historial-caja')
        ->assertOk()
        ->assertJsonPath('0.usuario.nombre', 'Admin');

    $this->assertDatabaseHas('admin_activity_logs', [
        'id_usuario' => $adminId,
        'nombre_usuario' => 'Admin',
        'rol_usuario' => 'Administrador',
        'accion' => 'cierre_caja',
    ]);

    $this->withSession(['usuario_id' => $cajeroId])
        ->getJson('/api/historial-caja')
        ->assertForbidden();
});

test('solo administradores pueden iniciar sesión y acceder a las rutas del sistema', function () {
    DB::table('rol_usuario')->insert(['id_rol' => 2, 'rol_nombre' => 'Cajero']);
    $cajeroId = DB::table('usuarios')->insertGetId([
        'nombre' => 'Cajero',
        'gmail' => 'cajero@gmail.com',
        'contrasenia' => Hash::make('clave-segura'),
        'id_rol_usuario' => 2,
    ]);

    $this->from('/')->post('/login', [
        'gmail' => 'cajero@gmail.com',
        'contrasenia' => 'clave-segura',
    ])->assertRedirect('/');

    $this->assertDatabaseMissing('admin_activity_logs', [
        'id_usuario' => $cajeroId,
        'accion' => 'ingreso_sistema',
    ]);

    DB::table('caja_estado')->insert([
        'abierta' => true,
        'monto_actual' => 500,
    ]);

    $this->withSession(['usuario_id' => $cajeroId])->get('/Inicio')->assertRedirect('/');
    $this->withSession(['usuario_id' => $cajeroId])
        ->postJson('/api/caja/abrir')
        ->assertForbidden();
    $this->withSession(['usuario_id' => $cajeroId])
        ->postJson('/api/caja/cerrar')
        ->assertForbidden();

    $this->assertDatabaseHas('caja_estado', [
        'abierta' => true,
        'monto_actual' => 500,
    ]);
});

test('el acceso Admin registra ingreso, apertura y cierre de caja', function () {
    $adminId = session('usuario_id');

    $this->post('/login', [
        'gmail' => 'admin-pruebas@gmail.com',
        'contrasenia' => 'clave-segura',
    ])->assertRedirect(route('home'));

    $this->postJson('/api/caja/abrir')->assertOk();
    $this->postJson('/api/caja/cerrar')->assertCreated();

    $this->assertDatabaseHas('admin_activity_logs', ['id_usuario' => $adminId, 'accion' => 'ingreso_sistema']);
    $this->assertDatabaseHas('admin_activity_logs', ['id_usuario' => $adminId, 'accion' => 'apertura_caja']);
    $this->assertDatabaseHas('admin_activity_logs', ['id_usuario' => $adminId, 'accion' => 'cierre_caja']);
});

test('el estado informa cuando la caja está cerrada', function () {
    $this->getJson('/api/caja/estado')
        ->assertOk()
        ->assertJsonPath('abierta', false)
        ->assertJsonPath('monto_actual', 0)
        ->assertJsonPath('mensaje', 'La caja está cerrada.');
});

test('el cobro suma su importe al total actual de la caja', function () {
    $tipoPagoId = DB::table('tipo_pago')->insertGetId(['tipo_pago' => 'Efectivo']);
    $pedidoId = DB::table('pedidos')->insertGetId(['monto_total' => 0]);
    DB::table('detalle_pedido')->insert([
        ['fk_id_pedido' => $pedidoId, 'subtotal' => 1250.50],
        ['fk_id_pedido' => $pedidoId, 'subtotal' => 799.50],
    ]);
    DB::table('caja_estado')->insert([
        'abierta' => true,
        'monto_actual' => 1000,
    ]);

    $this->post('/Mesas/pagar', [
        'id_pedido' => $pedidoId,
        'fk_tipo_pago' => $tipoPagoId,
    ])->assertRedirect();

    $segundoPedidoId = DB::table('pedidos')->insertGetId(['monto_total' => 0]);
    DB::table('detalle_pedido')->insert([
        'fk_id_pedido' => $segundoPedidoId,
        'subtotal' => 450,
    ]);

    $this->post('/Mesas/pagar', [
        'id_pedido' => $segundoPedidoId,
        'fk_tipo_pago' => $tipoPagoId,
    ])->assertRedirect();

    $this->assertDatabaseHas('registro_pago', [
        'fk_id_pedido' => $pedidoId,
        'monto' => 2050,
    ]);
    $this->assertDatabaseCount('registro_pago', 2);
    $this->assertDatabaseHas('caja_estado', [
        'abierta' => true,
        'monto_actual' => 3500,
    ]);

    $this->getJson('/api/caja/estado')
        ->assertOk()
        ->assertJsonPath('monto_actual', 3500);
});

test('el cobro no se registra cuando la caja está cerrada', function () {
    $tipoPagoId = DB::table('tipo_pago')->insertGetId(['tipo_pago' => 'Efectivo']);
    $pedidoId = DB::table('pedidos')->insertGetId(['monto_total' => 1000]);
    DB::table('detalle_pedido')->insert([
        'fk_id_pedido' => $pedidoId,
        'subtotal' => 1000,
    ]);

    $this->from('/Mesas')->post('/Mesas/pagar', [
        'id_pedido' => $pedidoId,
        'fk_tipo_pago' => $tipoPagoId,
    ])->assertSessionHasErrors('id_pedido');

    $this->assertDatabaseCount('registro_pago', 0);
});

test('la búsqueda de ventas filtra por parámetros y pagina los resultados', function () {
    $efectivoId = DB::table('tipo_pago')->insertGetId(['tipo_pago' => 'Efectivo']);
    $tarjetaId = DB::table('tipo_pago')->insertGetId(['tipo_pago' => 'Tarjeta']);

    $ventaCoincidenteId = DB::table('registro_pago')->insertGetId([
        'monto' => 1500,
        'fk_tipo_pago' => $efectivoId,
        'fecha_pago' => '2026-10-20',
    ]);
    $ventaTarjetaId = DB::table('registro_pago')->insertGetId([
        'monto' => 2500,
        'fk_tipo_pago' => $tarjetaId,
        'fecha_pago' => '2026-10-21',
    ]);
    $segundaVentaId = DB::table('registro_pago')->insertGetId([
        'monto' => 1500,
        'fk_tipo_pago' => $efectivoId,
        'fecha_pago' => '2026-10-19',
    ]);

    $this->getJson('/api/ventas?fk_tipo_pago='.$efectivoId.'&monto=1500&per_page=1')
        ->assertOk()
        ->assertJsonPath('data.0.id_registro', $ventaCoincidenteId)
        ->assertJsonPath('total', 2)
        ->assertJsonPath('per_page', 1);

    $this->getJson('/api/ventas?fk_tipo_pago='.$efectivoId.'&monto=1500&per_page=1&page=2')
        ->assertOk()
        ->assertJsonPath('data.0.id_registro', $segundaVentaId);

    $this->getJson('/api/ventas?fecha_desde=2026-10-21&fecha_hasta=2026-10-21')
        ->assertOk()
        ->assertJsonPath('data.0.id_registro', $ventaTarjetaId)
        ->assertJsonPath('total', 1);

    $this->getJson('/api/ventas?fecha_hasta=2026-10-20')
        ->assertOk()
        ->assertJsonPath('total', 2);

    $this->getJson('/api/ventas?per_page=101')->assertUnprocessable();
});

test('la página de ventas carga los tipos de pago y los controles de búsqueda', function () {
    DB::table('tipo_pago')->insert(['tipo_pago' => 'Efectivo']);

    $this->get('/Ventas')
        ->assertOk()
        ->assertViewIs('interfaz_ventas')
        ->assertSee('Efectivo')
        ->assertSee('id="filtrosVentas"', false)
        ->assertSee('js/ventas.js');
});
