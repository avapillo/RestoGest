<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
  <title>Usuarios - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_user_control.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <aside class="barra-lateral">
      <div class="logo-sistema">
        <img src="{{ asset('img/Sanwicheria Gabriel icon.png') }}" alt="Sandwichería Gabriel">
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('usuario.index') }}" class="opcion-menu activa">Control de Usuario</a>
        <a href="{{ route('ventas.index') }}" class="opcion-menu">Administración de Ventas</a>
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
        @if (session('error'))
          <div class="mensaje-error">
            {{ session('error') }}
          </div>
        @endif

        <div class="contenedor-botones-header">
          <button id="btnAbrirNuevoUsuario" class="btn-agregar">➕ Nuevo Usuario</button>
          <button id="btnAbrirModalRoles" class="btn-agregar">➕ Nuevo Rol</button>
        </div>

      </header>
<!-- GRILLA DE USUARIOS -->
<section id="grillaUsuario" class="grilla-usuario">

  @forelse ($usuarios as $usuario)
    <div class="tarjeta-usuario" id="producto-{{ $usuario->id }}">

      <div class="info-usuario">
        <h4>{{ $usuario->nombre }}</h4>
        <p class="rol-usuario">
          {{-- Muestra todos los roles concatenados con comas --}}
          Rol: <strong>{{ $usuario->roles->pluck('rol_nombre')->implode(', ') ?: 'Sin Rol' }}</strong>
        </p>
        <p class="precio-usuario"><strong>Gmail: {{ $usuario->gmail }}</strong></p>
      </div>

      <div class="acciones-tarjeta">
        {{-- Guardamos el array de IDs de roles en data-roles --}}
        <button class="btn-accion btn-modificar"
                data-id="{{ $usuario->id }}"
                data-nombre="{{ $usuario->nombre }}"
                data-gmail="{{ $usuario->gmail }}"
                data-roles='@json($usuario->roles->pluck("id_rol"))'>✏️ Modificar</button>

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
<div id="modalUsuario" class="modal-overlay {{ $errors->any() ? '' : 'hidden' }}">
  <div class="modal-content">
    <h3>Nuevo Usuario</h3>

    <form id="formUsuario" action="{{ route('usuario.store') }}" method="POST">
      @csrf

      @if ($errors->any())
        <div class="error-texto">{{ $errors->first() }}</div>
      @endif

      <div class="grupo-campo">
        <label for="nombre">Nombre del usuario:</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" required placeholder="Ej: Juan" value="{{ old('nombre') }}">
        @error('nombre') <span class="error-texto">{{ $message }}</span> @enderror
      </div>

      <div class="grupo-campo">
        <label for="gmail">Gmail:</label>
        <input type="email" id="gmail" name="gmail" required placeholder="Ej: juan@gmail.com" value="{{ old('gmail') }}" autocomplete="email">
        @error('gmail') <span class="error-texto">{{ $message }}</span> @enderror
      </div>

      <div class="grupo-campo">
        <label for="contrasenia">Contraseña:</label>
        <div class="campo-contrasenia">
          <input type="password" id="contrasenia" name="contrasenia" required minlength="8" autocomplete="new-password">
          <button type="button" class="btn-visibilidad-contrasenia" data-toggle-password="contrasenia" aria-label="Mostrar contraseña">Mostrar</button>
        </div>
        @error('contrasenia') <span class="error-texto">{{ $message }}</span> @enderror
      </div>

      <div class="grupo-campo">
        <label for="contrasenia_confirmation">Confirmar contraseña:</label>
        <div class="campo-contrasenia">
          <input type="password" id="contrasenia_confirmation" name="contrasenia_confirmation" required minlength="8" autocomplete="new-password">
          <button type="button" class="btn-visibilidad-contrasenia" data-toggle-password="contrasenia_confirmation" aria-label="Mostrar confirmación de contraseña">Mostrar</button>
        </div>
      </div>

      <!-- Contenedor dinámico de Comboboxes de Roles -->
      <div class="grupo-campo">
        <label>Rol Usuario:</label>
        @error('roles') <span class="error-texto">{{ $message }}</span> @enderror
        <div id="contenedorCombosNuevo">
          <div class="combo-rol-item" style="display: flex; gap: 8px; margin-bottom: 8px;">
            <select name="roles[]" required class="select-categoria">
              <option value="">-- Seleccionar Rol --</option>
              @foreach ($roles as $rol)
                <option value="{{ $rol->id_rol }}">{{ $rol->rol_nombre }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <button type="button" id="btnAgregarComboNuevo" class="btn-agregar" style="margin-top: 5px;">➕ Agregar otro rol</button>
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

    <form action="{{ route('usuario.update') }}" method="POST">
      @csrf
      <input type="hidden" id="edit_id" name="id">

      <div class="grupo-campo">
        <label for="edit_nombre">Nombre del usuario:</label>
        <input type="text" id="edit_nombre" name="nombre" required>
      </div>

      <div class="grupo-campo">
        <label for="edit_gmail">Gmail:</label>
        <input type="email" id="edit_gmail" name="gmail" required>
      </div>

      <div class="grupo-campo">
        <label for="edit_contrasenia">Contraseña:</label>
        <div class="campo-contrasenia">
          <input type="password" id="edit_contrasenia" name="contrasenia" minlength="8" autocomplete="new-password">
          <button type="button" class="btn-visibilidad-contrasenia" data-toggle-password="edit_contrasenia" aria-label="Mostrar nueva contraseña">Mostrar</button>
        </div>
        @error('contrasenia') <span class="error-texto">{{ $message }}</span> @enderror
      </div>

      <!-- Contenedor dinámico de Comboboxes en la Edición -->
      <div class="grupo-campo">
        <label>Roles:</label>
        <div id="contenedorCombosEditar">
          <!-- JavaScript insertará los selects correspondientes a cada rol aquí -->
        </div>

        <button type="button" id="btnAgregarComboEditar" class="btn-agregar" style="margin-top: 5px;">➕ Agregar otro rol</button>
      </div>

      <div class="modal-botones">
        <button type="button" id="btnCerrarModalEditar" class="btn-cancelar">Cancelar</button>
        <button type="submit" class="btn-guardar">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- ================== Modal de gestión de roles =================== -->
<div id="modalRoles" class="modal-overlay {{ old('_rol_form') === 'crear' ? '' : 'hidden' }}">
  <div class="modal-content modal-roles">
    <h3>Gestionar Roles</h3>

    <form id="formRol" action="{{ route('rol.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_rol_form" value="crear">
      <div class="grupo-campo">
        <label for="rol_nombre">Nombre del rol:</label>
        <input type="text" id="rol_nombre" name="rol_nombre" maxlength="50" required placeholder="Ej: Administrador" value="{{ old('rol_nombre') }}">
        @if (old('_rol_form') === 'crear')
          @error('rol_nombre') <span class="error-texto">{{ $message }}</span> @enderror
          @error('_rol_form') <span class="error-texto">{{ $message }}</span> @enderror
        @endif
      </div>
      <div class="modal-botones">
        <button type="button" id="btnCerrarModalRoles" class="btn-cancelar">Cancelar</button>
        <button type="submit" class="btn-guardar">Guardar Rol</button>
      </div>
    </form>

    <div class="separador-roles"></div>
    <h4 class="titulo-lista-roles">Roles registrados</h4>
    <div id="listaRoles" class="grilla-roles" data-url="{{ route('rol.index') }}">
      <p class="cargando-roles">Cargando roles...</p>
    </div>
  </div>
</div>

<div id="modalEditarRol" class="modal-overlay {{ old('_rol_form') === 'modificar' ? '' : 'hidden' }}">
  <div class="modal-content">
    <h3>Modificar Rol</h3>
    <form action="{{ route('rol.update') }}" method="POST">
      @csrf
      <input type="hidden" name="_rol_form" value="modificar">
      <input type="hidden" id="edit_rol_id" name="id_rol" value="{{ old('id_rol') }}">
      <div class="grupo-campo">
        <label for="edit_rol_nombre">Nombre del rol:</label>
        <input type="text" id="edit_rol_nombre" name="rol_nombre" maxlength="50" required value="{{ old('_rol_form') === 'modificar' ? old('rol_nombre') : '' }}">
        @if (old('_rol_form') === 'modificar')
          @error('rol_nombre') <span class="error-texto">{{ $message }}</span> @enderror
          @error('id_rol') <span class="error-texto">{{ $message }}</span> @enderror
          @error('_rol_form') <span class="error-texto">{{ $message }}</span> @enderror
        @endif
      </div>
      <div class="modal-botones">
        <button type="button" id="btnCerrarEditarRol" class="btn-cancelar">Cancelar</button>
        <button type="submit" class="btn-guardar">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

  <script src="{{ asset('js/usuarios.js') }}"></script>
</body>
</html>
