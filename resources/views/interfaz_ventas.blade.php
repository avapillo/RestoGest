<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
  <title>Ventas - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/ventas.css') }}">
</head>
<body>

  <!-- CONTENEDOR PRINCIPAL: Envuelve todo el sitio web -->
  <div class="contenedor-sitio">

    <!-- BARRA LATERAL (MENU) -->
    <aside class="barra-lateral">
      <div class="logo-sistema">
        <img src="{{ asset('img/Sanwicheria Gabriel icon.png') }}" alt="Sanwichería Gabriel">
      </div>

      <!-- Opciones del menú -->
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('usuario.index') }}" class="opcion-menu">Control de Usuario</a>
        <a href="{{ route('ventas.index') }}" class="opcion-menu activa">Administración de Ventas</a>
      </nav>
    </aside>

    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <div>
          <p class="sobre-titulo">Administración</p>
          <h1>Ventas</h1>
        </div>
        <p class="resumen-encabezado">Consulta pagos por fecha y tipo de pago.</p>
      </header>

      <section class="panel-filtros" aria-labelledby="tituloFiltros">
        <div class="titulo-panel">
          <div>
            <p class="sobre-titulo">Búsqueda parametrizada</p>
            <h2 id="tituloFiltros">Filtrar registros</h2>
          </div>
          <span class="indicador-filtros">Filtros opcionales</span>
        </div>

        <form id="filtrosVentas" class="formulario-filtros" data-api-url="{{ url('/api/ventas') }}" novalidate>
          <div class="rejilla-filtros">
            <div class="grupo-campo">
              <label for="fk_tipo_pago">Tipo de pago</label>
              <select id="fk_tipo_pago" name="fk_tipo_pago">
                <option value="">Todos los tipos</option>
                @foreach ($tiposPago as $tipoPago)
                  <option value="{{ $tipoPago->id }}">{{ $tipoPago->tipo_pago }}</option>
                @endforeach
              </select>
            </div>
            <div class="grupo-campo">
              <label for="fecha_desde">Desde</label>
              <input id="fecha_desde" name="fecha_desde" type="date">
            </div>
            <div class="grupo-campo">
              <label for="fecha_hasta">Hasta</label>
              <input id="fecha_hasta" name="fecha_hasta" type="date">
            </div>
          </div>

          <div class="acciones-filtros">
            <button class="btn-buscar" type="submit">
              <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg>
              Buscar ventas
            </button>
            <button class="btn-limpiar" type="reset">Limpiar filtros</button>
          </div>
        </form>
      </section>

      <section class="panel-resultados" aria-labelledby="tituloResultados">
        <div class="encabezado-resultados">
          <div>
            <p class="sobre-titulo">Registros</p>
            <h2 id="tituloResultados">Resultados de ventas</h2>
          </div>
          <p id="resumenResultados" class="resumen-resultados" aria-live="polite">Cargando ventas...</p>
        </div>
        <p id="estadoVentas" class="estado-ventas" role="status" aria-live="polite">Cargando ventas...</p>

        <div class="envoltura-tabla">
          <table class="tabla-ventas">
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Monto</th>
                <th scope="col">Tipo de pago</th>
                <th scope="col">Quién cerró caja</th>
              </tr>
            </thead>
            <tbody id="filasVentas">
              <tr><td class="fila-vacia" colspan="4">Cargando registros...</td></tr>
            </tbody>
          </table>
        </div>

        <footer class="paginacion-ventas">
          <span id="paginaActual">Página 1 de 1</span>
          <div class="controles-paginacion">
            <button id="paginaAnterior" type="button" aria-label="Página anterior" disabled>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>
            </button>
            <button id="paginaSiguiente" type="button" aria-label="Página siguiente" disabled>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
            </button>
          </div>
        </footer>
      </section>
    </main>
  </div>

  <script src="{{ asset('js/ventas.js') }}" defer></script>
</body>
</html>
