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

    <!-- BARRA LATERAL -->
    <aside class="barra-lateral">
        <div class="logo-sistema">
            <h3>Sandwichería Gabriel</h3>
        </div>

        <nav class="menu-navegacion">
            <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
            <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
            <a href="{{ route('producto.index') }}" class="opcion-menu activa">Producto</a>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal">

        <header class="encabezado-seccion">
            <h2>Gestión de Productos</h2>

            @if (session('status'))
                <div id="mensajeStatus" class="mensaje-exito">
                    {{ session('status') }}
                </div>
            @endif

            <div class="contenedor-botones-header">
                <button id="btnAbrirModalProducto" class="btn-agregar">
                    ➕ Nuevo Producto
                </button>
            </div>
        </header>

        <!-- FILTROS DE CATEGORÍAS -->
        <div class="contenedor-filtros">

            <button
                class="btn-filtro {{ $categoriaSeleccionada == 'todas' ? 'activo' : '' }}"
                data-id="todas">
                Todas
            </button>

            @foreach ($categorias as $cat)
                <button
                    class="btn-filtro {{ $categoriaSeleccionada == $cat->id ? 'activo' : '' }}"
                    data-id="{{ $cat->id }}">
                    {{ $cat->categoria }}
                </button>
            @endforeach

        </div>

        <!-- TARJETAS DE PRODUCTOS -->
        <section id="grillaProductos" class="grilla-productos">

            @forelse ($productos as $producto)

                <div class="tarjeta-producto" id="producto-{{ $producto->id }}">

                    <!-- IMAGEN DEL PRODUCTO -->
                    <div style="
                        width: 100%;
                        height: 180px;
                        overflow: hidden;
                        background: #101a30;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 12px 12px 0 0;
                    ">

                        @if (!empty($producto->imagen))

                            <img
                                src="{{ \Illuminate\Support\Str::startsWith($producto->imagen, ['http://', 'https://']) ? $producto->imagen : asset('storage/' . ltrim($producto->imagen, '/')) }}"
                                alt="{{ $producto->nombre }}"
                                loading="lazy"
                                style="
                                    display: block;
                                    width: 100%;
                                    height: 180px;
                                    object-fit: cover;
                                "
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >

                            <div style="
                                display: none;
                                width: 100%;
                                height: 180px;
                                align-items: center;
                                justify-content: center;
                                color: #ffffff;
                                font-size: 42px;
                            ">
                                🖼️
                            </div>

                        @else

                            <div style="
                                display: flex;
                                width: 100%;
                                height: 180px;
                                align-items: center;
                                justify-content: center;
                                color: #ffffff;
                                font-size: 42px;
                            ">
                                🖼️
                            </div>

                        @endif

                    </div>

                    <!-- INFORMACIÓN DEL PRODUCTO -->
                    <div class="info-producto">
                        <h4>{{ $producto->nombre }}</h4>

                        <p class="categoria-etiqueta">
                            Categoría:
                            <strong>
                                {{ $producto->categoria->categoria ?? 'Sin Categoría' }}
                            </strong>
                        </p>

                        <p class="precio-producto">
                            <strong>${{ $producto->precio }}</strong>
                        </p>
                    </div>

                    <!-- ACCIONES -->
                    <div class="acciones-tarjeta">

                        <button
                            class="btn-accion btn-modificar"
                            data-id="{{ $producto->id }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio="{{ $producto->precio }}"
                            data-fk_id_categoria="{{ $producto->fk_id_categoria }}">
                            ✏️ Modificar
                        </button>

                        <form
                            action="{{ route('producto.destroy', $producto->id) }}"
                            method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este producto?')"
                            style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn-accion btn-eliminar">
                                🗑️ Eliminar
                            </button>

                        </form>

                    </div>

                </div>

            @empty

                <p class="sin-productos">
                    No hay productos registrados en esta categoría.
                </p>

            @endforelse

        </section>

    </main>

</div>

<!-- MODAL: REGISTRAR PRODUCTO -->
<div id="modalProducto" class="modal-overlay hidden">
    <div class="modal-content">

        <h3>Agregar Nuevo Producto</h3>

        <form
            id="formProducto"
            action="{{ route('producto.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="grupo-campo">
                <label for="nombre">Nombre del Producto:</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    required
                    placeholder="Ej: Lomito Completo"
                    value="{{ old('nombre') }}">

                @error('nombre')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <div class="grupo-campo">
                <label for="fk_id_categoria">Categoría:</label>

                <select
                    id="fk_id_categoria"
                    name="fk_id_categoria"
                    required
                    class="select-categoria">

                    <option value="">-- Selecciona una categoría --</option>

                    @foreach ($categorias as $cat)
                        <option
                            value="{{ $cat->id }}"
                            {{ old('fk_id_categoria') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->categoria }}
                        </option>
                    @endforeach

                </select>

                @error('fk_id_categoria')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <div class="grupo-campo">
                <label for="precio">Precio ($):</label>
                <input
                    type="number"
                    id="precio"
                    name="precio"
                    required
                    min="0"
                    placeholder="Ej: 3500"
                    value="{{ old('precio') }}">

                @error('precio')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <div class="grupo-campo">
                <label for="imagen">Imagen del Producto:</label>
                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    accept="image/*">

                @error('imagen')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <div class="modal-botones">
                <button type="button" id="btnCerrarModal" class="btn-cancelar">
                    Cancelar
                </button>

                <button type="submit" class="btn-guardar">
                    Guardar Producto
                </button>
            </div>

        </form>
    </div>
</div>

<!-- MODAL: MODIFICAR PRODUCTO -->
<div id="modalEditarProducto" class="modal-overlay hidden">
    <div class="modal-content">

        <h3>Modificar Producto</h3>

        <form
            action="{{ route('producto.update') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <input type="hidden" id="edit_id" name="id">

            <div class="grupo-campo">
                <label for="edit_nombre">Nombre del Producto:</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>

            <div class="grupo-campo">
                <label for="edit_fk_id_categoria">Categoría:</label>

                <select
                    id="edit_fk_id_categoria"
                    name="fk_id_categoria"
                    required
                    class="select-categoria">

                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">
                            {{ $cat->categoria }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="grupo-campo">
                <label for="edit_precio">Precio ($):</label>
                <input type="number" id="edit_precio" name="precio" required min="0">
            </div>

            <div class="grupo-campo">
                <label for="edit_imagen">Nueva Imagen (Opcional):</label>
                <input
                    type="file"
                    id="edit_imagen"
                    name="imagen"
                    accept="image/*">
            </div>

            <div class="modal-botones">
                <button
                    type="button"
                    id="btnCerrarModalEditar"
                    class="btn-cancelar">
                    Cancelar
                </button>

                <button type="submit" class="btn-guardar">
                    Guardar Cambios
                </button>
            </div>

        </form>
    </div>
</div>

<!-- MODAL: REGISTRAR CATEGORÍA -->
<div id="modalCategoria" class="modal-overlay hidden">
    <div class="modal-content">

        <h3>Agregar Nueva Categoría</h3>

        <form
            id="formCategoria"
            action="{{ route('categoria.store') }}"
            method="POST">

            @csrf

            <div class="grupo-campo">
                <label for="input_categoria">Nombre de la Categoría:</label>

                <input
                    type="text"
                    id="input_categoria"
                    name="categoria"
                    required
                    placeholder="Ej: Desayunos"
                    value="{{ old('categoria') }}">

                @error('categoria')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <div class="modal-botones">
                <button
                    type="button"
                    id="btnCerrarModalCategoria"
                    class="btn-cancelar">
                    Cancelar
                </button>

                <button type="submit" class="btn-guardar">
                    Guardar Categoría
                </button>
            </div>

        </form>
    </div>
</div>

<script src="{{ asset('js/producto.js') }}"></script>

</body>
</html>