<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mesas - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_producto.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('intefaz_mesa') }}" class="opcion-menu activa">Mesas</a>
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
      </header>

      <!-- BOTONES DE FILTRADO DE SECCIONES -->
<div class="contenedor-filtros">
  <button class="btn-filtro {{ ($seccionSeleccionada ?? 'todas') == 'todas' ? 'activo' : '' }}" data-id="">
    Todas
  </button>

  @foreach ($todasSecciones as $sec)
    <button class="btn-filtro {{ ($seccionSeleccionada ?? '') == $sec->id_seccion ? 'activo' : '' }}" data-id="{{ $sec->id_seccion }}">
      {{ $sec->seccion }}
    </button>
  @endforeach
</div>

      <!-- GRILLA DE TARJETAS DE MESA (SIN IMÁGENES) -->
      @forelse ($secciones as $seccion)
        <div class="bloque-seccion" style="margin-bottom: 30px;">
          <h3 style="border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; color: #0f172a;">
            📍 {{ $seccion->seccion }}
          </h3>

          <section class="grilla-productos">
            @forelse ($seccion->mesas as $mesa)
              @php $pedido = $mesa->pedidoActivo; @endphp

              <div class="tarjeta-mesa" id="mesa-{{ $mesa->id_mesa }}">
                <div class="info-producto">
                  <h4>Mesa N° {{ $mesa->numero_mesa }}</h4>

                  @if($pedido)
                    <div class="contenedor-consumo">
                      <strong>Consumo:</strong>
                      <ul class="lista-consumo">
                        @foreach ($pedido->detalles as $detalle)
                          <li>
                            {{ $detalle->cantidad }}x
                            {{ $detalle->producto->nombre ?? $detalle->combo->combo ?? 'Ítem' }}
                          </li>
                        @endforeach
                      </ul>
                    </div>

                    <p class="precio-producto">
                      <strong>Total: ${{ number_format($pedido->monto_total, 2) }}</strong>
                    </p>
                  @else
                    <p class="categoria-etiqueta" style="color: #16a34a; font-weight: bold; margin: 15px 0;">Disponible</p>
                    <p class="precio-producto"><strong>$0.00</strong></p>
                  @endif
                </div>

                <div class="acciones-tarjeta">
                  @if($pedido)
                    <button class="btn-accion btn-modificar btnAbrirModalEditar"
                            data-id_pedido="{{ $pedido->id_pedido }}"
                            data-numero_mesa="{{ $mesa->numero_mesa }}"
                            data-detalles="{{ json_encode($pedido->detalles) }}">
                      ✏️ Modificar
                    </button>

                    <button class="btn-accion btn-pagar btnAbrirModalPago"
                            data-id_pedido="{{ $pedido->id_pedido }}"
                            data-numero_mesa="{{ $mesa->numero_mesa }}"
                            data-monto="{{ $pedido->monto_total }}">
                      💳 Pagar
                    </button>
                  @else
                    <button class="btn-accion btn-disabled" disabled>Sin Pedido</button>
                  @endif
                </div>
              </div>
            @empty
              <p class="sin-productos">No hay mesas en esta sección.</p>
            @endforelse
          </section>
        </div>
      @empty
        <p class="sin-productos">No existen secciones registradas.</p>
      @endforelse

    </main>
  </div>

  <!-- MODAL 1: MODIFICAR PEDIDO (GRILLA DE EDICIÓN DE ÍTEMS) -->
  <div id="modalEditarPedido" class="modal-overlay hidden">
    <div class="modal-content modal-ancho-grilla">
      <h3>Modificar Pedido - Mesa N° <span id="lblMesaEditar"></span></h3>

      <form action="{{ route('pedidos.actualizar') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_id_pedido" name="id_pedido">

        <table class="tabla-grilla-pedidos">
          <thead>
            <tr>
              <th>Producto / Combo</th>
              <th style="width: 100px;">Cantidad</th>
              <th style="width: 80px; text-align: center;">Acción</th>
            </tr>
          </thead>
          <tbody id="contenedorDetallesEditar">
            <!-- Filas dinámicas generadas mediante JavaScript -->
          </tbody>
        </table>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModalEditar" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 2: PROCESAR PAGO -->
  <div id="modalPago" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Pagar Pedido - Mesa N° <span id="lblNumeroMesa"></span></h3>

      <form action="{{ route('pedidos.pagar') }}" method="POST">
        @csrf
        <input type="hidden" id="pago_id_pedido" name="id_pedido">

        <div class="grupo-campo">
          <label>Total a Pagar ($):</label>
          <input type="text" id="pago_monto_total" readonly style="font-weight: bold; background-color: #f4f4f4;">
        </div>

        <div class="grupo-campo">
          <label for="fk_id_tipo_pago">Método de Pago:</label>
          <select id="fk_id_tipo_pago" name="fk_id_tipo_pago" required class="select-categoria">
            <option value="">-- Seleccione método --</option>
            @foreach ($metodosPago as $metodo)
              <option value="{{ $metodo->id }}">{{ $metodo->combo }}</option>
            @endforeach
          </select>
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModalPago" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar" style="background-color: #16a34a;">Confirmar Pago</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/mesas.js') }}"></script>
</body>
</html>
