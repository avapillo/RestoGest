<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_user_control.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('intefaz_mesa') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('usuario.index') }}" class="opcion-menu activa">Control de Usuario</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
      </nav>
    </aside>

    <main class="contenido-principal">
      <header class="encabezado-seccion">
        <h2>Control de Usuarios</h2>

        <!-- Mensaje de confirmación/éxito -->
        @if (session('status'))
          <div id="mensajeStatus" class="mensaje-exito">
            {{ session('status') }}
          </div>
        @endif

        <div class="contenedor-botones-header">
          <button id="btnAbrirNuevoUsuario" class="btn-agregar">➕ Nuevo Usuario</button>
        </div>

      </header>


      <!-- GRILLA DE Usuarios -->
      <section id="grillaUsuario" class="grilla-usuario">

        @forelse ($usuarios as $usuario)
          <div class="tarjeta-usuario" id="producto-{{ $usuario->id }}">

            <div class="info-usuario">
              <h4>{{ $usuario->nombre }}</h4>
              <p class="rol-usuario">
                Rol: <strong>{{ $usuario->roles->rol_nombre ?? 'Sin Rol' }}</strong>
              </p>
              <p class="precio-usuario"><strong>Contraseña: ********</strong></p>
            </div>

            <div class="acciones-tarjeta">

              <button class="btn-accion btn-modificar"
                      data-id="{{ $usuario->id }}"
                      data-nombre="{{ $usuario->nombre }}"
                      data-contrasenia="{{ $usuario->contrasenia }}"
                      data-fk_id_categoria="{{ $usuario->id_rol_usuario }}">✏️ Modificar</button>

              <form action="{{ route('usuario.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-accion btn-eliminar">🗑️ Eliminar</button>
              </form>
            </div>
          </div>
        @empty
          <p class="sin-usuario">No hay usuarios registrados.</p>
        @endforelse

      </section>
    </main>

    <!-- ================== Modal de Nuevos usuarios =================== -->
    <div id="modalUsuario" class="modal-overlay hidden">
     <div class="modal-content">
      <h3>Nuevo Usuario</h3>

      <form id="formUsuario" action="{{ route('usuario.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grupo-campo">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan" value="{{ old('nombre') }}">
          @error('nombre') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="contrasenia">Contraseña:</label>
          <input type="text" id="contrasenia" name="contrasenia" required placeholder="Ej: 2580" value="{{ old('contrasenia') }}">
          @error('contrasenia') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="grupo-campo">
          <label for="id_rol_usuario">Rol Usuario:</label>
          <select id="id_rol_usuario" name="id_rol_usuario" required class="select-categoria">
            <option value="">-- Seleccionar Rol --</option>
            @foreach ($roles as $rol)
              <option value="{{ $rol->id_rol }}" {{ old('id_rol_usuario') == $rol->id_rol ? 'selected' : '' }}>
                {{ $rol->rol_nombre }}
              </option>
            @endforeach
          </select>
          @error('id_rol_usuario') <span class="error-texto">{{ $message }}</span> @enderror
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModal" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Registrar Usuario</button>
        </div>
      </form>
     </div>
  </div>

  <!-- ================== Modal de Modificar usuarios =================== -->
  <div id="modalEditarUsuario" class="modal-overlay hidden">
    <div class="modal-content">
      <h3>Modificar Usuario</h3>

      <form action="{{ route('usuario.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="edit_id" name="id">

        <div class="grupo-campo">
          <label for="edit_nombre">Nombre:</label>
          <input type="text" id="edit_nombre" name="nombre" required>
        </div>

        <div class="grupo-campo">
          <label for="edit_contraseña">Contraseña:</label>
          <input type="text" id="edit_contraseña" name="contrasenia" required>
        </div>

        <div class="grupo-campo">
          <label for="edit_fk_id_categoria">Rol:</label>
          <!-- ✅ Corregido name e id a fk_id_categoria -->
          <select id="edit_id_rol" name="id_rol_usuario" required class="select-categoria">
            @foreach ($roles as $rol)
              <option value="{{ $rol->id_rol }}">{{ $rol->rol_nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="modal-botones">
          <button type="button" id="btnCerrarModalEditar" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/usuarios.js') }}"></script>
</body>
</html>
