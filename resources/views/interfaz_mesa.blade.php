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
        <a href="{{ route('mesas.index') }}" class="opcion-menu activo">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="#" class="opcion-menu">Ventas</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <h2>Gestión de Pedidos</h2>

        <!-- Mensaje de confirmación/éxito -->
        @if (session('status'))
          <div id="mensajeStatus" class="mensaje-exito">
            {{ session('status') }}
          </div>
        @endif

        <div class="contenedor-botones-header">
          <button id="btnAbrirModalProducto" class="btn-agregar">➕ Nuevo Mesa NO FUNCIONA/button>
          <!-- <button id="btnAbrirModalCategoria" class="btn-agregar">➕ Nueva Categoría</button> -->
        </div>

      </header>

      <!-- BOTONES DE FILTRADO DE CATEGORÍAS -->
      <div class="contenedor-filtros">
         <!-- Botón para ver todos los productos -->

         <!-- Botones dinámicos desde la BD -->
         @foreach ($seccion as $secc)
          <button
            class="btn-filtro {{ $seccionSelecionada == $secc->id_seccion ? 'activo' : '' }}"
            data-id="{{ $secc->id }}">
            {{ $secc->seccion }}
          </button>
         @endforeach
      </div>

      <!-- GRILLA DE TARJETAS DE PRODUCTOS -->



    </main>

  </div>


  <script src="{{ asset('js/producto.js') }}"></script>
</body>
</html>
