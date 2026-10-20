@forelse ($roles as $rol)
  <article class="tarjeta-rol">
    <strong class="nombre-rol">{{ $rol->rol_nombre }}</strong>
    <div class="acciones-rol">
      <button type="button"
              class="btn-accion btn-modificar btn-modificar-rol"
              data-id="{{ $rol->id_rol }}"
              data-nombre="{{ $rol->rol_nombre }}">
        Modificar
      </button>
      <form action="{{ route('rol.destroy', $rol->id_rol) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
      </form>
    </div>
  </article>
@empty
  <p class="sin-roles">Todavía no hay roles registrados.</p>
@endforelse
