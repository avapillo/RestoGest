<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->integer('id_rol')->primary();
            $table->string('rol_nombre', 50)->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('nombre', 50)->nullable();
            $table->integer('contrasenia')->nullable();
            $table->integer('id_rol_usuario')->nullable();
            $table->string('gmail', 255)->unique('uk_usuarios_gmail');
            $table->unique('nombre', 'uq_nombre');

            $table->foreign('id_rol_usuario', 'fk_usuarios_rol')
                ->references('id_rol')
                ->on('rol_usuario');
        });

        Schema::create('usuario_roles', function (Blueprint $table) {
            $table->integer('fk_id_usuario');
            $table->integer('fk_id_rol');
            $table->primary(['fk_id_usuario', 'fk_id_rol']);
            $table->index('fk_id_rol', 'fk_usuario_roles_rol');

            $table->foreign('fk_id_usuario', 'fk_usuario_roles_usuario')
                ->references('id')
                ->on('usuarios')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('fk_id_rol', 'fk_usuario_roles_rol_foreign')
                ->references('id_rol')
                ->on('rol_usuario')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('categoria', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('categoria', 50)->nullable();
        });

        Schema::create('combos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('combo', 50)->nullable();
        });

        Schema::create('tipo_pago', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('tipo_pago', 45)->nullable();
        });

        Schema::create('seccion', function (Blueprint $table) {
            $table->integer('id_seccion')->autoIncrement()->primary();
            $table->string('seccion', 60)->nullable();
        });

        Schema::create('mesas', function (Blueprint $table) {
            $table->integer('id_mesa')->autoIncrement()->primary();
            $table->integer('numero_mesa')->nullable();
            $table->integer('fk_id_seccion')->nullable();

            $table->foreign('fk_id_seccion', 'fk_id_seccion')
                ->references('id_seccion')
                ->on('seccion');
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('nombre', 60)->nullable();
            $table->integer('precio');
            $table->string('imagen', 255)->nullable();
            $table->integer('fk_id_categoria')->nullable();

            $table->foreign('fk_id_categoria', 'fk_id_categoria')
                ->references('id')
                ->on('categoria');
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->integer('id_pedido')->autoIncrement()->primary();
            $table->decimal('monto_total', 10, 2)->nullable();
            $table->time('hora_pedido')->default(DB::raw('(curtime())'));
            $table->integer('fk_id_mesa')->nullable();
            $table->integer('fk_id_tipo_pago')->nullable();
            $table->boolean('es_para_llevar')->default(false);

            $table->foreign('fk_id_mesa', 'fk_pedidos_mesa')
                ->references('id_mesa')
                ->on('mesas');
            $table->foreign('fk_id_tipo_pago', 'fk_pedidos_pago')
                ->references('id')
                ->on('tipo_pago');
        });

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->integer('id_detalle')->autoIncrement()->primary();
            $table->integer('fk_id_pedido');
            $table->integer('fk_id_producto')->nullable();
            $table->integer('fk_id_combo')->nullable();
            $table->integer('cantidad')->default(1);
            $table->integer('subtotal')->nullable();
            $table->integer('precio_unitario')->nullable();
            $table->string('descripcion', 255)->nullable();

            $table->foreign('fk_id_pedido', 'fk_detalle_pedido')
                ->references('id_pedido')
                ->on('pedidos')
                ->cascadeOnDelete();
            $table->foreign('fk_id_producto', 'fk_detalle_producto')
                ->references('id')
                ->on('productos');
            $table->foreign('fk_id_combo', 'fk_detalle_combo')
                ->references('id')
                ->on('combos');
        });

        Schema::create('historial_caja', function (Blueprint $table) {
            $table->integer('id_historial')->autoIncrement()->primary();
            $table->decimal('monto_total', 10, 0)->nullable();
            $table->dateTime('momento_cierre')->nullable();
            $table->integer('id_usuario')->nullable();

            $table->foreign('id_usuario', 'fk_historial_caja_usuario')
                ->references('id')
                ->on('usuarios');
        });

        Schema::create('registro_pago', function (Blueprint $table) {
            $table->integer('id_registro')->autoIncrement()->primary();
            $table->decimal('monto', 10, 0)->nullable();
            $table->integer('fk_tipo_pago')->nullable();
            $table->date('fecha_pago')->nullable()->default(DB::raw('(CURRENT_DATE)'));
            $table->integer('fk_id_historial')->nullable();

            $table->foreign('fk_tipo_pago', 'fk_tipo_pago')
                ->references('id')
                ->on('tipo_pago');
            $table->foreign('fk_id_historial', 'fk_registro_pago_historial')
                ->references('id_historial')
                ->on('historial_caja')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_pago');
        Schema::dropIfExists('historial_caja');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('seccion');
        Schema::dropIfExists('tipo_pago');
        Schema::dropIfExists('combos');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('usuario_roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('rol_usuario');
    }
};
