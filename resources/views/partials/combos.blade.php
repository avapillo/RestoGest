@forelse ($combos as $combo)
  <article class="tarjeta-seccion">
    <strong class="nombre-seccion">{{ $combo->combo }}</strong>
    <div class="acciones-seccion">
      <button type="button"
              class="btn-accion btn-modificar btn-modificar-combo"
              data-id="{{ $combo->id }}"
              data-nombre="{{ $combo->combo }}">
        Modificar
      </button>
      <form action="{{ route('combo.destroy', $combo->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este combo?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
      </form>
    </div>
  </article>
@empty
  <p class="sin-secciones">Todavía no hay combos registrados.</p>
@endforelse
