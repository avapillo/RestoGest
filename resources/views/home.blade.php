<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
  <title>Panel de Control - Sanwichería Gabriel</title>
  <!-- Enlace al archivo de estilos CSS en la carpeta public/css -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
        <a href="{{ route('home') }}" class="opcion-menu activa">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('usuario.index') }}" class="opcion-menu">Control de Usuario</a>
        <a href="{{ route('mantenimiento') }}" class="opcion-menu">Ventas</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL: Todo lo que cambia según la pantalla -->
    <main class="contenido-principal">
     <header class="barra-superior">

  <!-- COLUMNA IZQUIERDA -->
  <div class="columna-izquierda-caja">

    <!-- TÍTULO -->
    <div class="titulo-caja">
      <h2>Historial de Caja</h2>
    </div>

    <!-- BOTONES DE CAJA -->
    <div class="controles-caja">

      <button
        id="btn-abrir-caja"
        class="btn-abrir-caja"
      >
        Abrir Caja
      </button>

      <button
        id="btn-cerrar-caja"
        class="btn-cerrar-caja"
      >
        Cerrar Caja
      </button>

    </div>

  </div>


  <!-- COLUMNA DERECHA -->
  <div class="panel-caja">

    <!-- TOTAL RECAUDADO -->
    <div class="total-caja">

      <span>Total del día</span>

      <strong>
        $ <span id="total-pagos">0,00</span>
      </strong>

    </div>


    <!-- MENSAJE DE SINCRONIZACIÓN -->
    <div
      id="estado-api"
      class="mensaje-estado"
    >
      Cargando datos de la API...
    </div>

  </div>

</header>

      <!-- SECCIÓN DE LA TABLA -->
      <section class="seccion-tabla">
        <table id="tabla-datos">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Hora de Cierre</th>
              <th>Monto Total</th>
              <th>Cerro Caja</th>
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
