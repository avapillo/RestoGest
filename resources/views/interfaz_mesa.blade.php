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
        <a href="#" class="opcion-menu">Ventas</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
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
                <li style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                  <span>
                    <strong>{{ $detalle->cantidad }}x</strong> {{ $nombreItem }}
                  </span>
                  <span>${{ number_format($subtotalItem, 2) }}</span>
                </li>
              @endforeach
            </ul>

            <!-- Total calculado a partir de los importes de cada línea -->
            <div style="text-align: right; font-weight: bold; font-size: 0.95rem; border-top: 1px solid #eee; padding-top: 4px;">
              Total: ${{ number_format($totalPedidoCalculado, 2) }}
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

      <form id="formProducto" action="{{ route('cargar.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grupo-campo">
          <label for="fk_id_seccion">Seleccionar Area:</label>
          <select id="fk_id_seccion" name="fk_id_seccion" required class="select-categoria">
            <option value=""> Seleccionar seccion </option>
            @foreach ($secciones as $sec)
              <option value="{{ $sec->id_seccion }}" {{ old('fk_id_seccion', $seccionSeleccionada ?? '') == $sec->id_seccion ? 'selected' : '' }}>
                {{ $sec->seccion }}
              </option>
            @endforeach
          </select>
          @error('fk_id_seccion') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="fk_id_mesa">Seleccionar Mesa:</label>
          <select id="fk_id_mesa" name="fk_id_mesa" required class="select-categoria" {{ empty($seccionSeleccionada) ? 'disabled' : '' }}>
            <option value=""> Seleccionar Mesa </option>
            @foreach ($mesas as $mesa)
              <option value="{{ $mesa->id_mesa }}" {{ old('fk_id_mesa') == $mesa->id_mesa ? 'selected' : '' }}>
                Mesa N° {{ $mesa->numero_mesa }}
              </option>
            @endforeach
          </select>
          @error('fk_id_mesa') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="precio">Precio ($):</label>
          <input type="number" id="precio" name="precio" required placeholder="Ej: 3500" value="{{ old('precio') }}">
          @error('precio') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModal" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Registrar Pedido</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/pedidos.js') }}"></script>
</body>
</html>
