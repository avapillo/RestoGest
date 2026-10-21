@forelse ($secciones as $seccion)
  <article class="tarjeta-seccion">
    <strong class="nombre-seccion">{{ $seccion->seccion }}</strong>
    <div class="acciones-seccion">
      <button type="button"
              class="btn-accion btn-modificar btn-modificar-seccion"
              data-id="{{ $seccion->id_seccion }}"
              data-nombre="{{ $seccion->seccion }}">
        Modificar
      </button>
      <form action="{{ route('seccion.destroy', $seccion->id_seccion) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta sección?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
      </form>
    </div>
  </article>
@empty
  <p class="sin-secciones">Todavía no hay secciones registradas.</p>
@endforelse
