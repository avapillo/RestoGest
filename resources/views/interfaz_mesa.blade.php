<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mesas - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_mesa.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu activa">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
      </nav>
    </aside>

    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <h2>Atención de Mesas</h2>


        @if (session('status'))
          <div id="mensajeStatus" class="mensaje-exito">
            {{ session('status') }}
          </div>
        @endif

        <div class="contenedor-botones-header">
        <button id="btnAbrirModalProducto" class="btn-agregar">➕ Tomar Pedido</button>
          <!-- <button id="btnAbrirModalCategoria" class="btn-agregar">➕ Nueva Categoría</button> -->
        </div>


        <div class="contenedor-botones-header">
          <button id="btnAbrirModalProducto" class="btn-agregar">➕ Nueva Mesa</button>
          <!-- <button id="btnAbrirModalCategoria" class="btn-agregar">➕ Nueva Categoría</button> -->
        </div>

      </header>


      <!-- BOTONES DE FILTRADO DE SECCIONES -->
      <div class="contenedor-filtros">
        <button
         class="btn-filtro {{ $seccionSeleccionada == 'todas' ? 'activo' : '' }}"
        data-id="todas">
          Todas
        </button>

        @foreach ($secciones as $sec)
          <button class="btn-filtro {{ ($seccionSeleccionada ?? '') == $sec->id_seccion ? 'activo' : '' }}" data-id="{{ $sec->id_seccion }}">
            {{ $sec->seccion }}
          </button>
        @endforeach
      </div>

      <!-- GRILLA DE MESAS -->
<section id="grillaProductos" class="grilla-productos">

  @forelse ($mesas ?? [] as $mesa)
    @php
      // Tomamos el último pedido activo registrado en la mesa
      $ultimoPedido = $mesa->pedidos->last();
      $itemsEditables = $ultimoPedido?->detalles
        ->filter(fn ($detalle) => $detalle->fk_id_producto)
        ->map(fn ($detalle) => [
          'fk_id_categoria' => $detalle->producto->fk_id_categoria,
          'fk_id_producto' => $detalle->fk_id_producto,
          'cantidad' => $detalle->cantidad,
          'descripcion' => $detalle->descripcion,
        ])
        ->values() ?? collect();
      $pedidoTieneCombos = $ultimoPedido?->detalles->contains(fn ($detalle) => $detalle->fk_id_combo) ?? false;
    @endphp

    <div class="tarjeta-producto" id="mesa-{{ $mesa->id_mesa }}">

      <div class="info-producto">
        <!-- Título de la Mesa con la Hora al lado -->
        <h4>
          Mesa N° {{ $mesa->numero_mesa }}
          @if($ultimoPedido)
            <span style="font-size: 0.85rem; font-weight: normal; color: #555; margin-left: 6px;">
              ({{ $ultimoPedido->hora_pedido }})
            </span>
          @endif
        </h4>

        <p class="categoria-etiqueta">
          Sección: <strong>{{ $mesa->seccion->seccion ?? 'Sin Sección' }}</strong>
        </p>

        <!-- Bloque con el detalle de Productos/Combos -->
        @if ($ultimoPedido && $ultimoPedido->detalles->isNotEmpty())
          <div class="detalle-pedido" style="margin-top: 10px; border-top: 1px dashed #ccc; padding-top: 8px;">
            @php $totalPedidoCalculado = 0; @endphp
            <ul style="list-style: none; padding-left: 0; margin: 0 0 8px 0; font-size: 0.88rem;">
              @foreach ($ultimoPedido->detalles as $detalle)
                @php
                  // Determina si el detalle pertenece a un Producto o a un Combo
                  $nombreItem = $detalle->producto->nombre ?? $detalle->combo->nombre ?? 'Ítem';
                  $precioUnitario = $detalle->producto->precio ?? 0;
                  $subtotalItem = $detalle->producto
                    ? $precioUnitario * $detalle->cantidad
                    : $detalle->subtotal;
                  $totalPedidoCalculado += $subtotalItem;
                @endphp
                <li style="display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 12px; row-gap: 2px; margin-bottom: 6px;">
                  <span>
                    <strong>{{ $detalle->cantidad }}x</strong> {{ $nombreItem }}
                  </span>
                  <span>${{ number_format($subtotalItem, 2) }}</span>
                  @if ($detalle->descripcion)
                    <small style="grid-column: 1; color: #64748b; font-size: 0.8rem;">
                      {{ $detalle->descripcion }}
                    </small>
                  @endif
                </li>
              @endforeach
            </ul>

            <!-- Total calculado a partir de los importes de cada línea -->
            <div style="text-align: right; font-weight: bold; font-size: 0.95rem; border-top: 1px solid #eee; padding-top: 4px;">
              Total: ${{ number_format($totalPedidoCalculado, 2) }}
            </div>
            <div class="acciones-pedido">
              <button
                type="button"
                class="btn-accion btn-modificar btn-pedido-modificar"
                data-update-url="{{ route('pedido.update', $ultimoPedido->id_pedido) }}"
                data-items="{{ $itemsEditables->toJson() }}"
                data-seccion-id="{{ $mesa->fk_id_seccion }}"
                data-mesa-id="{{ $mesa->id_mesa }}"
                data-para-llevar="{{ (int) $ultimoPedido->es_para_llevar }}"
                @disabled($pedidoTieneCombos)
                title="{{ $pedidoTieneCombos ? 'No se pueden modificar pedidos que contienen combos desde este formulario.' : 'Modificar pedido' }}">
                Modificar
              </button>
              <button
                type="button"
                class="btn-accion btn-pedido-cobrar"
                data-order-id="{{ $ultimoPedido->id_pedido }}"
                data-total="{{ $totalPedidoCalculado }}">
                Cobrar
              </button>
            </div>
          </div>
        @endif


      </div>

    </div>
  @empty
    <p class="sin-productos">No hay mesas registradas en esta sección.</p>
  @endforelse

</section>


    </main>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 1: REGISTRAR PEDIDOS                 -->
  <!-- ========================================== -->
  <div id="modalProducto" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Agregar Pedido</h3>

      <form id="formProducto" action="{{ route('cargar.store') }}" data-create-url="{{ route('cargar.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="fila-horizontal contenedor-mesas-area">
          <div class="grupo-campo">
            <label for="fk_id_seccion">Sección:</label>
            <select id="fk_id_seccion" name="fk_id_seccion" required class="select-categoria">
              <option value="">Seleccionar sección</option>
              @foreach ($secciones as $sec)
                <option value="{{ $sec->id_seccion }}" {{ old('fk_id_seccion', $seccionSeleccionada ?? '') == $sec->id_seccion ? 'selected' : '' }}>
                  {{ $sec->seccion }}
                </option>
              @endforeach
            </select>
            @error('fk_id_seccion') <span class="error-texto">{{ $message }}</span> @enderror
          </div>

          <div class="grupo-campo">
            <label for="fk_id_mesa">Mesa:</label>
            <select id="fk_id_mesa" name="fk_id_mesa" required class="select-categoria" {{ empty($seccionSeleccionada) ? 'disabled' : '' }}>
              <option value="">Seleccionar mesa</option>
              @foreach ($mesas as $mesa)
                <option value="{{ $mesa->id_mesa }}" {{ old('fk_id_mesa') == $mesa->id_mesa ? 'selected' : '' }}>
                  Mesa N° {{ $mesa->numero_mesa }}
                </option>
              @endforeach
            </select>
            @error('fk_id_mesa') <span class="error-texto">{{ $message }}</span> @enderror
          </div>

          <div class="grupo-campo">
            <label for="es_para_llevar">Tipo de consumo:</label>
            <select id="es_para_llevar" name="es_para_llevar" required class="select-categoria">
              <option value="0" {{ old('es_para_llevar', '0') == '0' ? 'selected' : '' }}>Consumo en local</option>
              <option value="1" {{ old('es_para_llevar') == '1' ? 'selected' : '' }}>Para llevar</option>
            </select>
          </div>
        </div>

        <div id="listaProductosPedido">
          <div class="fila-item-producto">
            <div class="campos-item-producto">
              <div class="grupo-campo col-categoria">
                <label for="categoria-item-0">Categoría:</label>
                <select id="categoria-item-0" name="items[0][fk_id_categoria]" class="select-categoria selector-categoria" required>
                  <option value="">Seleccionar categoría</option>
                  @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                  @endforeach
                </select>
              </div>

              <div class="grupo-campo col-producto">
                <label for="producto-item-0">Producto:</label>
                <select id="producto-item-0" name="items[0][fk_id_producto]" class="select-categoria selector-producto" required disabled>
                  <option value="">Seleccionar producto</option>
                  @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}" data-categoria="{{ $producto->fk_id_categoria }}">
                      {{ $producto->nombre }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="grupo-campo col-cantidad">
                <label for="cantidad-item-0">Cantidad:</label>
                <input id="cantidad-item-0" type="number" name="items[0][cantidad]" min="1" value="1" required>
              </div>

              <div class="grupo-campo col-descripcion">
                <label for="descripcion-item-0">Descripción:</label>
                <input id="descripcion-item-0" type="text" name="items[0][descripcion]" maxlength="255" placeholder="Nota del producto">
              </div>
            </div>

            <div class="acciones-item-producto" aria-label="Acciones del producto">
              <button type="button" class="btn-agregar-item" data-accion="agregar" aria-label="Agregar otro producto" title="Agregar otro producto">+</button>
              <button type="button" class="btn-quitar-item" data-accion="quitar" aria-label="Quitar este producto" title="Quitar este producto">x</button>
            </div>
          </div>
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModal" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Registrar Pedido</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modalCobrar" class="modal-overlay hidden">
    <div class="modal-content modal-pago-content">
      <h3>Cobrar pedido</h3>
      <form id="formCobrar" action="{{ route('pedido.pagar') }}" method="POST">
        @csrf
        <input id="pagoIdPedido" type="hidden" name="id_pedido">
        <p class="total-cobro">Total: <strong id="totalCobroPedido"></strong></p>
        <div class="grupo-campo">
          <label for="fk_tipo_pago">Medio de pago:</label>
          <select id="fk_tipo_pago" name="fk_tipo_pago" class="select-categoria" required>
            <option value="">Seleccionar medio de pago</option>
            @foreach ($tiposPago as $tipoPago)
              <option value="{{ $tipoPago->id }}">{{ $tipoPago->tipo_pago }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarModalCobro" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar" {{ $tiposPago->isEmpty() ? 'disabled' : '' }}>Confirmar cobro</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/pedidos.js') }}"></script>
</body>
</html>
