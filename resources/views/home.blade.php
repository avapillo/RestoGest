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
        <a href="{{ route('ventas.index') }}" class="opcion-menu">Administración de Ventas</a>
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

    <!-- MONTOS DE CAJA -->
    <div class="total-caja total-caja-manual">
      <label for="monto-manual">Monto manual</label>
      <textarea id="monto-manual" rows="1" maxlength="20" inputmode="numeric" aria-label="Monto manual, solo números"></textarea>
    </div>

    <div class="total-caja">
      <span>Monto total del día</span>
      <strong>$ <span id="total-pagos">0,00</span></strong>
    </div>

    <section class="resumen-gastos" aria-labelledby="titulo-total-gastos">
      <div>
        <span id="titulo-total-gastos">Total de gasto del día</span>
        <strong>$ <span id="total-gastos">0,00</span></strong>
      </div>
      <button id="btn-abrir-gastos" class="btn-abrir-gastos" type="button" aria-haspopup="dialog" aria-controls="modal-gastos">
        Registrar gastos
      </button>
    </section>

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

  <div id="modal-gastos" class="modal-gastos" hidden>
    <section class="contenido-modal-gastos" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-gastos">
      <header class="encabezado-modal-gastos">
        <div>
          <p>Control de egresos</p>
          <h2 id="titulo-modal-gastos">Registrar gastos</h2>
        </div>
        <button id="btn-cerrar-gastos" class="btn-cerrar-gastos" type="button" aria-label="Cerrar">&times;</button>
      </header>

      <form id="formulario-gastos">
        <div id="filas-formulario-gastos" class="filas-formulario-gastos">
          <div class="fila-form-gasto">
            <label class="campo-gasto">
              <span>Nombre</span>
              <input name="producto[]" type="text" maxlength="120" placeholder="Ej. Aceite" required>
            </label>
            <label class="campo-gasto">
              <span>Monto</span>
              <input name="monto[]" type="number" min="0.01" step="0.01" placeholder="0,00" required>
            </label>
            <button class="btn-quitar-fila-gasto" type="button" aria-label="Quitar gasto" title="Quitar gasto" disabled>&times;</button>
          </div>
        </div>

        <template id="plantilla-fila-gasto">
          <div class="fila-form-gasto">
            <label class="campo-gasto">
              <span>Nombre</span>
              <input name="producto[]" type="text" maxlength="120" placeholder="Ej. Aceite" required>
            </label>
            <label class="campo-gasto">
              <span>Monto</span>
              <input name="monto[]" type="number" min="0.01" step="0.01" placeholder="0,00" required>
            </label>
            <button class="btn-quitar-fila-gasto" type="button" aria-label="Quitar gasto" title="Quitar gasto">&times;</button>
          </div>
        </template>

        <p id="estado-registro-gastos" class="estado-registro-gastos" role="status" aria-live="polite"></p>
        <div class="acciones-modal-gastos">
          <button id="btn-agregar-fila-gasto" class="btn-agregar-fila-gasto" type="button">+ Agregar otro</button>
          <div>
            <button id="btn-cancelar-gastos" class="btn-cancelar-gastos" type="button">Cancelar</button>
            <button class="btn-guardar-gastos" type="submit">Guardar gastos</button>
          </div>
        </div>
      </form>

      <section class="registros-gastos" aria-labelledby="titulo-registros-gastos">
        <h3 id="titulo-registros-gastos">Gastos registrados</h3>
        <ul id="lista-gastos" class="lista-gastos"></ul>
      </section>
    </section>
  </div>

  <div id="modal-mensaje-cierre" class="modal-mensaje-cierre" hidden>
    <section class="contenido-modal-mensaje-cierre" role="alertdialog" aria-modal="true" aria-labelledby="titulo-mensaje-cierre">
      <h2 id="titulo-mensaje-cierre">No se puede cerrar caja</h2>
      <p id="texto-mensaje-cierre"></p>
      <button id="btn-cerrar-mensaje-cierre" type="button">Entendido</button>
    </section>
  </div>

  <!-- Enlace al archivo JavaScript en la carpeta public/js -->
  <script src="{{ asset('js/home.js') }}"></script>
</body>
</html>
