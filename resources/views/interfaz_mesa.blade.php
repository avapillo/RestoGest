<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
  <title>Mesas - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_mesa.css') }}">
  <link rel="stylesheet" href="{{ asset('css/responsive-adapter.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <aside class="barra-lateral">
      <div class="logo-sistema">
        <img src="{{ asset('img/Sanwicheria Gabriel icon.png') }}" alt="Sandwichería Gabriel">
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu activa">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('usuario.index') }}" class="opcion-menu">Control de Usuario</a>
        <a href="{{ route('ventas.index') }}" class="opcion-menu">Administración de Ventas</a>
        <a href="{{ route('pendientes.index') }}" class="opcion-menu">Pendientes</a>
      </nav>

      <div class="cerrar-sesion-contenedor">
        <a href="{{ route('logout') }}" class="cerrar-sesion">
          <svg xmlns="http://www.w3.org/2000/svg"
               width="24" height="24"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="2"
               stroke-linecap="round"
               stroke-linejoin="round"
               aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Cerrar sesión</span>
        </a>
      </div>

    </aside>

    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <h2>Atención de Mesas</h2>


        @if (session('status'))
          <div id="mensajeStatus" class="mensaje-exito">
            {{ session('status') }}
          </div>
        @endif
        @if (session('error'))
          <div class="mensaje-error">
            {{ session('error') }}
          </div>
        @endif
        @if ($errors->has('fk_id_mesa') || ($errors->has('id_pedido') && $errors->first('id_pedido') !== 'No se puede cobrar un pedido que aún no está listo.'))
          <div class="aviso-caja-cerrada" role="alert">
            {{ $errors->first('fk_id_mesa') ?: $errors->first('id_pedido') }}
          </div>
        @endif
        <div
          id="avisoCajaPedidos"
          class="aviso-caja-cerrada"
          data-url="{{ url('/api/caja/estado') }}"
          role="status"
          hidden></div>

        <div class="contenedor-botones-header">
          <button id="btnAbrirModalMesa" class="btn-agregar">➕ Nueva Mesa</button>
          <button id="btnAbrirModalSecciones" class="btn-agregar">➕ Nueva Sección</button>
          <!-- <button id="btnAbrirModalCombos" class="btn-agregar">➕ Agregar Combo</button> -->
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
        @if ($ultimoPedido)
          <p class="categoria-etiqueta">
            {{ $ultimoPedido->rol_creador ?? 'Responsable' }}:
            <strong>{{ $ultimoPedido->nombre_creador ?? 'No registrado' }}</strong>
          </p>
        @endif


@if ($mesa->pedidos->isNotEmpty())

    @php
        $totalConsumoMesa = 0;
        $totalPedidoCalculado = 0;

        // Variables para controlar si se puede cobrar la mesa.
        $hayPedidosComidaMesa = false;
        $comidaMesaListaParaCobrar = true;

        // Revisamos todos los pedidos pendientes de esta mesa.
        foreach ($mesa->pedidos as $pedidoEstado) {

            $soloBebidasEstado = $pedidoEstado->detalles->isNotEmpty();

            foreach ($pedidoEstado->detalles as $detalleEstado) {

                $categoriaEstado = mb_strtolower(trim(
                    (string) ($detalleEstado->producto?->categoria?->categoria ?? '')
                ));

                if (
                    !$detalleEstado->producto ||
                    mb_strpos($categoriaEstado, 'bebida') === false
                ) {
                    $soloBebidasEstado = false;
                    break;
                }
            }

            if (!$soloBebidasEstado) {

                $hayPedidosComidaMesa = true;

                $estadoMesa = mb_strtolower(trim(
                    (string) ($pedidoEstado->estado->estado ?? '')
                ));

                if ($estadoMesa !== 'servido') {
                    $comidaMesaListaParaCobrar = false;
                }
            }
        }
    @endphp

    <div class="detalle-pedido" style="margin-top: 10px; border-top: 1px dashed #ccc; padding-top: 8px;">

        @foreach ($mesa->pedidos as $pedidoMesa)

            @php
                $estadoNombre = trim($pedidoMesa->estado->estado ?? 'Recibido');
                $estadoNormalizado = mb_strtolower($estadoNombre); 
                $pedidoSoloBebidas = $pedidoMesa->detalles->isNotEmpty();

foreach ($pedidoMesa->detalles as $detalleBebida) {
    $categoriaBebida = mb_strtolower(trim(
        (string) ($detalleBebida->producto?->categoria?->categoria ?? '')
    ));

    if (
        !$detalleBebida->producto ||
        mb_strpos($categoriaBebida, 'bebida') === false
    ) {
        $pedidoSoloBebidas = false;
        break;
    }
}
                $pedidoSoloBebidas = $pedidoMesa->detalles->isNotEmpty();

foreach ($pedidoMesa->detalles as $detalleBebida) {
    $categoriaBebida = mb_strtolower(trim(
        (string) ($detalleBebida->producto?->categoria?->categoria ?? '')
    ));

    if (!$detalleBebida->producto || mb_strpos($categoriaBebida, 'bebida') === false) {
        $pedidoSoloBebidas = false;
        break;
    }
}

                if ($estadoNormalizado === 'listo') {
                    $colorEstado = '#22A565';
                } elseif (
                    $estadoNormalizado === 'preparando' ||
                    $estadoNormalizado === 'en preparación' ||
                    $estadoNormalizado === 'en preparacion'
                ) {
                    $colorEstado = '#FFC107';
                } elseif ($estadoNormalizado === 'servido') {
                    $colorEstado = '#0D6EFD';
                } else {
                    $colorEstado = '#DC3545';
                }

                $totalPedidoActual = 0;
            @endphp

            <div style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed #ddd;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">

                    <strong>Pedido #{{ $pedidoMesa->id_pedido }}</strong>

                    <span style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem;">

                        <span
                            title="Estado: {{ $estadoNombre }}"
                            aria-label="Estado: {{ $estadoNombre }}"
                            style="
                                display: inline-block;
                                width: 12px;
                                height: 12px;
                                border-radius: 50%;
                                background-color: {{ $colorEstado }};
                                border: 2px solid white;
                                box-shadow: 0 0 0 1px rgba(0,0,0,0.12);
                            ">
                        </span>

                        {{ $estadoNombre }}

                    </span>

                </div>

                <ul style="list-style: none; padding-left: 0; margin: 0; font-size: 0.88rem;">

                    @foreach ($pedidoMesa->detalles as $detalle)

                        @php
                            $nombreItem = $detalle->producto->nombre ?? $detalle->combo->nombre ?? 'Ítem';

// Se utiliza el mismo subtotal histórico que utiliza el controlador al cobrar.
$subtotalItem = (float) ($detalle->subtotal ?? 0);

$totalPedidoActual += $subtotalItem;
$totalConsumoMesa += $subtotalItem;
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

                <div style="text-align: right; font-size: 0.85rem; font-weight: bold;">
                    Subtotal pedido: ${{ number_format($totalPedidoActual, 2) }}
                </div>

            </div>

            {{-- Conservamos el total del último pedido para el botón de cobro actual. --}}
            @if ($ultimoPedido && $pedidoMesa->id_pedido == $ultimoPedido->id_pedido)
                @php
                    $totalPedidoCalculado = $totalPedidoActual;
                @endphp
            @endif

        @endforeach

        <div style="text-align: right; font-weight: bold; font-size: 1rem; border-top: 1px solid #eee; padding-top: 8px; margin-top: 8px;">
            Total consumo de la mesa: ${{ number_format($totalConsumoMesa, 2) }}
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
                class="btn-accion btn-tomar-pedido"
                disabled
                data-seccion-id="{{ $mesa->fk_id_seccion }}"
                data-seccion-nombre="{{ $mesa->seccion->seccion ?? 'Sin Sección' }}"
                data-mesa-numero="{{ $mesa->numero_mesa }}"
                data-mesa-id="{{ $mesa->id_mesa }}">
                ➕ Agregar pedido
            </button>

            @if ($hayPedidosComidaMesa)
    <button
        type="button"
        class="btn-accion btn-pedido-cobrar"
        disabled
        data-tipo-cobro="mesa"
        data-puede-cobrar="{{ $comidaMesaListaParaCobrar ? '1' : '0' }}"
        data-mesa-id="{{ $mesa->id_mesa }}"
        data-total="{{ $totalConsumoMesa }}">
        Cobrar
    </button>
@else
    <button
        type="button"
        class="btn-accion btn-pedido-cobrar"
        disabled
        data-tipo-cobro="pedido"
        data-puede-cobrar="1"
        data-order-id="{{ $ultimoPedido->id_pedido }}"
        data-total="{{ $totalPedidoCalculado }}">
        Cobrar
    </button>
@endif

        </div>

    </div>

@endif


        @if ($mesa->pedidos->isEmpty())
          <button
            type="button"
            class="btn-agregar btn-tomar-pedido"
            disabled
            data-seccion-id="{{ $mesa->fk_id_seccion }}"
            data-seccion-nombre="{{ $mesa->seccion->seccion ?? 'Sin Sección' }}"
            data-mesa-numero="{{ $mesa->numero_mesa }}"
            data-mesa-id="{{ $mesa->id_mesa }}">
            ➕ Tomar Pedido
          </button>
        @endif

      </div>

    </div>
  @empty
    <p class="sin-productos">No hay mesas registradas en esta sección.</p>
  @endforelse

</section>


    </main>
  </div>

  <div id="modalMesa" class="modal-overlay {{ old('_mesa_form') === 'crear' ? '' : 'hidden' }}">
    <div class="modal-content">
      <h3>Nueva Mesa</h3>
      <form action="{{ route('mesas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_mesa_form" value="crear">
        <div class="grupo-campo">
          <label for="numero_mesa">Número de mesa:</label>
          <input type="number" id="numero_mesa" name="numero_mesa" min="1" required value="{{ old('numero_mesa') }}">
          @if (old('_mesa_form') === 'crear')
            @error('numero_mesa') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="grupo-campo">
          <label for="fk_id_seccion_mesa">Sección:</label>
          <select id="fk_id_seccion_mesa" name="fk_id_seccion" required class="select-categoria">
            <option value="">Seleccionar sección</option>
            @foreach ($secciones as $sec)
              <option value="{{ $sec->id_seccion }}" {{ old('fk_id_seccion') == $sec->id_seccion ? 'selected' : '' }}>
                {{ $sec->seccion }}
              </option>
            @endforeach
          </select>
          @if (old('_mesa_form') === 'crear')
            @error('fk_id_seccion') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarModalMesa" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar" {{ $secciones->isEmpty() ? 'disabled' : '' }}>Guardar Mesa</button>
        </div>
      </form>
    </div>
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
          <input id="fk_id_seccion" type="hidden" name="fk_id_seccion" required>
          <input id="fk_id_mesa" type="hidden" name="fk_id_mesa" required>
          <div class="grupo-campo">
            <label>Sección:</label>
            <p id="nombreSeccionPedido"></p>
          </div>

          <div class="grupo-campo">
            <label>Mesa:</label>
            <p id="numeroMesaPedido"></p>
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
        <input id="pagoIdMesa" type="hidden" name="fk_id_mesa">
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

  @if ($errors->first('id_pedido') === 'No se puede cobrar un pedido que aún no está listo.')
    <div id="modalMensajePedidoIncompleto" class="modal-overlay">
      <section class="modal-content modal-error-pedido" role="alertdialog" aria-modal="true" aria-labelledby="tituloErrorPedido">
        <h3 id="tituloErrorPedido">No se puede cobrar el pedido</h3>
        <p>{{ $errors->first('id_pedido') }}</p>
        <button id="btnCerrarErrorPedido" class="btn-guardar" type="button">Entendido</button>
      </section>
    </div>
  @endif

  <div id="modalSecciones" class="modal-overlay {{ old('_seccion_form') === 'crear' ? '' : 'hidden' }}">
    <div class="modal-content modal-secciones">
      <h3>Gestionar Secciones</h3>
      <form action="{{ route('seccion.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_seccion_form" value="crear">
        <div class="grupo-campo">
          <label for="seccion_nombre">Nombre de la sección:</label>
          <input type="text" id="seccion_nombre" name="seccion" maxlength="60" pattern="\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*" title="El nombre solo puede contener letras y espacios." required placeholder="Ej: Terraza" value="{{ old('seccion') }}">
          @if (old('_seccion_form') === 'crear')
            @error('seccion') <span class="error-texto">{{ $message }}</span> @enderror
            @error('_seccion_form') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarModalSecciones" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Sección</button>
        </div>
      </form>

      <div class="separador-secciones"></div>
      <h4 class="titulo-lista-secciones">Secciones registradas</h4>
      <div id="listaSecciones" class="grilla-secciones" data-url="{{ route('seccion.index') }}">
        <p class="cargando-secciones">Cargando secciones...</p>
      </div>
    </div>
  </div>

  <div id="modalEditarSeccion" class="modal-overlay {{ old('_seccion_form') === 'modificar' ? '' : 'hidden' }}">
    <div class="modal-content">
      <h3>Modificar Sección</h3>
      <form action="{{ route('seccion.update') }}" method="POST">
        @csrf
        <input type="hidden" name="_seccion_form" value="modificar">
        <input type="hidden" id="edit_seccion_id" name="id_seccion" value="{{ old('id_seccion') }}">
        <div class="grupo-campo">
          <label for="edit_seccion_nombre">Nombre de la sección:</label>
          <input type="text" id="edit_seccion_nombre" name="seccion" maxlength="60" pattern="\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*" title="El nombre solo puede contener letras y espacios." required value="{{ old('_seccion_form') === 'modificar' ? old('seccion') : '' }}">
          @if (old('_seccion_form') === 'modificar')
            @error('seccion') <span class="error-texto">{{ $message }}</span> @enderror
            @error('id_seccion') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarEditarSeccion" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modalCombos" class="modal-overlay {{ old('_combo_form') === 'crear' ? '' : 'hidden' }}">
    <div class="modal-content modal-secciones">
      <h3>Gestionar Combos</h3>
      <form action="{{ route('combo.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_combo_form" value="crear">
        <div class="grupo-campo">
          <label for="combo_nombre">Nombre del combo:</label>
          <input type="text" id="combo_nombre" name="combo" maxlength="50" required placeholder="Ej: Hamburguesa completa" value="{{ old('combo') }}">
          @if (old('_combo_form') === 'crear')
            @error('combo') <span class="error-texto">{{ $message }}</span> @enderror
            @error('_combo_form') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarModalCombos" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Combo</button>
        </div>
      </form>

      <div class="separador-secciones"></div>
      <h4 class="titulo-lista-secciones">Combos registrados</h4>
      <div id="listaCombos" class="grilla-secciones" data-url="{{ route('combo.index') }}">
        <p class="cargando-secciones">Cargando combos...</p>
      </div>
    </div>
  </div>

  <div id="modalEditarCombo" class="modal-overlay {{ old('_combo_form') === 'modificar' ? '' : 'hidden' }}">
    <div class="modal-content">
      <h3>Modificar Combo</h3>
      <form action="{{ route('combo.update') }}" method="POST">
        @csrf
        <input type="hidden" name="_combo_form" value="modificar">
        <input type="hidden" id="edit_combo_id" name="id_combo" value="{{ old('id_combo') }}">
        <div class="grupo-campo">
          <label for="edit_combo_nombre">Nombre del combo:</label>
          <input type="text" id="edit_combo_nombre" name="combo" maxlength="50" required value="{{ old('_combo_form') === 'modificar' ? old('combo') : '' }}">
          @if (old('_combo_form') === 'modificar')
            @error('combo') <span class="error-texto">{{ $message }}</span> @enderror
            @error('id_combo') <span class="error-texto">{{ $message }}</span> @enderror
          @endif
        </div>
        <div class="modal-botones">
          <button type="button" id="btnCerrarEditarCombo" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/pedidos.js') }}"></script>
</body>
</html>
