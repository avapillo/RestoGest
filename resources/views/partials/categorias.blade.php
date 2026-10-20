@forelse ($categorias as $categoria)
  <article class="tarjeta-categoria">
    <strong class="nombre-categoria">{{ $categoria->categoria }}</strong>
    <div class="acciones-categoria">
      <button type="button"
              class="btn-accion btn-modificar btn-modificar-categoria"
              data-id="{{ $categoria->id }}"
              data-nombre="{{ $categoria->categoria }}">
        Modificar
      </button>
      <form action="{{ route('categoria.destroy', $categoria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
      </form>
    </div>
  </article>
@empty
  <p class="sin-categorias">Todavía no hay categorías registradas.</p>
@endforelse
