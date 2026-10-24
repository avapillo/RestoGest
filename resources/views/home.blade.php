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

      <!-- Botón para cerrar sesión -->
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
