<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Control - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_producto.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <!-- BARRA LATERAL (MENU) -->
    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu activa">Producto</a>
        <a href="#" class="opcion-menu">Ventas</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <h2>Gestión de Productos</h2>

        <!-- Mensaje de confirmación/éxito -->
        @if (session('status'))
          <div id="mensajeStatus" class="mensaje-exito">
            {{ session('status') }}
          </div>
        @endif

        <div class="contenedor-botones-header">
          <button id="btnAbrirModalProducto" class="btn-agregar">➕ Nuevo Producto</button>
          <!-- <button id="btnAbrirModalCategoria" class="btn-agregar">➕ Nueva Categoría</button> -->
        </div>

      </header>

      <!-- BOTONES DE FILTRADO DE CATEGORÍAS -->
      <div class="contenedor-filtros">
        <!-- Botón para ver todos los productos -->
        <button
          class="btn-filtro {{ $categoriaSeleccionada == 'todas' ? 'activo' : '' }}"
          data-id="todas">
          Todas
        </button>

        <!-- Botones dinámicos desde la BD -->
        @foreach ($categorias as $cat)
          <button
            class="btn-filtro {{ $categoriaSeleccionada == $cat->id ? 'activo' : '' }}"
            data-id="{{ $cat->id }}">
            {{ $cat->categoria }}
          </button>
        @endforeach
      </div>

      <!-- GRILLA DE TARJETAS DE PRODUCTOS -->
      <section id="grillaProductos" class="grilla-productos">

        @forelse ($productos as $producto)
          <div class="tarjeta-producto" id="producto-{{ $producto->id }}">

            <div class="info-producto">
              <h4>{{ $producto->nombre }}</h4>
              <p class="categoria-etiqueta">
                Categoría: <strong>{{ $producto->categoria->categoria ?? 'Sin Categoría' }}</strong>
              </p>
              <p class="precio-producto"><strong>${{ $producto->precio }}</strong></p>
            </div>

            <div class="acciones-tarjeta">
              <!-- ✅ Corregido data-fk_id_categoria -->
              <button class="btn-accion btn-modificar"
                      data-id="{{ $producto->id }}"
                      data-nombre="{{ $producto->nombre }}"
                      data-precio="{{ $producto->precio }}"
                      data-fk_id_categoria="{{ $producto->fk_id_categoria }}">✏️ Modificar</button>

              <form action="{{ route('producto.destroy', $producto->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?')" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-accion btn-eliminar">🗑️ Eliminar</button>
              </form>
            </div>
          </div>
        @empty
          <p class="sin-productos">No hay productos registrados en esta categoría.</p>
        @endforelse

      </section>
    </main>

  </div>

  <!-- ========================================== -->
  <!-- MODAL 1: REGISTRAR PRODUCTO                -->
  <!-- ========================================== -->
  <div id="modalProducto" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Agregar Nuevo Producto</h3>

      <form id="formProducto" action="{{ route('producto.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grupo-campo">
          <label for="nombre">Nombre del Producto:</label>
          <input type="text" id="nombre" name="nombre" required placeholder="Ej: Lomito Completo" value="{{ old('nombre') }}">
          @error('nombre') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="fk_id_categoria">Categoría:</label>
          <!-- ✅ Corregido name e id a fk_id_categoria -->
          <select id="fk_id_categoria" name="fk_id_categoria" required class="select-categoria">
            <option value="">-- Selecciona una categoría --</option>
            @foreach ($categorias as $cat)
              <option value="{{ $cat->id }}" {{ old('fk_id_categoria') == $cat->id ? 'selected' : '' }}>
                {{ $cat->categoria }}
              </option>
            @endforeach
          </select>
          @error('fk_id_categoria') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="precio">Precio ($):</label>
          <input type="number" id="precio" name="precio" required placeholder="Ej: 3500" value="{{ old('precio') }}">
          @error('precio') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="imagen">Imagen del Producto:</label>
          <input type="file" id="imagen" name="imagen" accept="image/*">
          @error('imagen') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModal" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Producto</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 2: MODIFICAR PRODUCTO                -->
  <!-- ========================================== -->
  <div id="modalEditarProducto" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Modificar Producto</h3>

      <form action="{{ route('producto.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="edit_id" name="id">

        <div class="grupo-campo">
          <label for="edit_nombre">Nombre del Producto:</label>
          <input type="text" id="edit_nombre" name="nombre" required>
        </div>

        <div class="grupo-campo">
          <label for="edit_fk_id_categoria">Categoría:</label>
          <!-- ✅ Corregido name e id a fk_id_categoria -->
          <select id="edit_fk_id_categoria" name="fk_id_categoria" required class="select-categoria">
            @foreach ($categorias as $cat)
              <option value="{{ $cat->id }}">{{ $cat->categoria }}</option>
            @endforeach
          </select>
        </div>

        <div class="grupo-campo">
          <label for="edit_precio">Precio ($):</label>
          <input type="number" id="edit_precio" name="precio" required>
        </div>

        <div class="grupo-campo">
          <label for="edit_imagen">Nueva Imagen (Opcional):</label>
          <input type="file" id="edit_imagen" name="imagen" accept="image/*">
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModalEditar" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 3: REGISTRAR CATEGORÍA              -->
  <!-- ========================================== -->
  <div id="modalCategoria" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Agregar Nueva Categoría</h3>

      <form id="formCategoria" action="{{ route('categoria.store') }}" method="POST">
        @csrf

        <div class="grupo-campo">
          <label for="input_categoria">Nombre de la Categoría:</label>
          <!-- ✅ Corregido name a 'categoria' para el CategoriaController -->
          <input type="text" id="input_categoria" name="categoria" required placeholder="Ej: Desayunos" value="{{ old('categoria') }}">
          @error('categoria') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModalCategoria" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Categoría</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/producto.js') }}"></script>
</body>
</html>
