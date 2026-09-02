<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Control - Cevichería Gabriel</title>
  <!-- Enlace al archivo de estilos CSS en la carpeta public/css -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

  <!-- CONTENEDOR PRINCIPAL: Envuelve todo el sitio web -->
  <div class="contenedor-sitio">

    <!-- BARRA LATERAL (MENU) -->
    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>

      <!-- Opciones del menú -->
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu activa">Principal</a>
        <a href="{{ route('intefaz_mesa') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="#" class="opcion-menu">Ventas</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL: Todo lo que cambia según la pantalla -->
    <main class="contenido-principal">
      <header class="barra-superior">
        <div class="header-info">
          <h2>Historial de Caja</h2>

          <!-- BOTONERA HORIZONTAL Y SUMADOR -->
          <div class="panel-caja-acciones">
            <button id="btn-abrir-caja" class="btn-accion btn-abrir">Abrir Caja</button>

            <div class="sumador-container">
              Total Pagos: $<span id="total-pagos">0.00</span>
            </div>

            <button id="btn-cerrar-caja" class="btn-accion btn-cerrar" disabled>Cerrar Caja</button>
          </div>
        </div>

        <div id="estado-api" class="mensaje-estado">Cargando datos de la API...</div>
      </header>

      <!-- SECCIÓN DE LA TABLA -->
      <section class="seccion-tabla">
        <table id="tabla-datos">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Hora de Cierre</th>
              <th>Monto Total</th>
              <th>Cerrado Por</th> <!-- NUEVA COLUMNA -->
            </tr>
          </thead>
          <tbody>
            <!-- Las filas se inyectarán aquí dinámicamente usando JavaScript -->
          </tbody>
        </table>
      </section>
    </main>

  </div>

  <!-- Enlace al archivo JavaScript en la carpeta public/js -->
  <script src="{{ asset('js/home.js') }}"></script>
</body>
</html>
