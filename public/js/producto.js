document.addEventListener('DOMContentLoaded', () => {
  // ----------------------------------------------------
  // 1. MODAL AGREGAR PRODUCTO
  // ----------------------------------------------------
  const btnAbrirModalProducto = document.getElementById('btnAbrirModalProducto');
  const btnCerrarModalProducto = document.getElementById('btnCerrarModal');
  const modalProducto = document.getElementById('modalProducto');
  const formProducto = document.getElementById('formProducto');

  if (btnAbrirModalProducto && modalProducto) {
    btnAbrirModalProducto.addEventListener('click', () => {
      modalProducto.classList.remove('hidden');
    });
  }

  if (btnCerrarModalProducto && modalProducto) {
    btnCerrarModalProducto.addEventListener('click', () => {
      modalProducto.classList.add('hidden');
      if (formProducto) formProducto.reset(); // Limpia los campos al cerrar
    });
  }

  // ----------------------------------------------------
  // 2. MODAL REGISTRO CATEGORÍA
  // ----------------------------------------------------
  const btnAbrirModalCategoria = document.getElementById('btnAbrirModalCategoria');
  const btnCerrarModalCategoria = document.getElementById('btnCerrarModalCategoria'); // ✅ Agregado
  const modalCategoria = document.getElementById('modalCategoria');
  const formCategoria = document.getElementById('formCategoria');
  const listaCategorias = document.getElementById('listaCategorias');
  const modalEditarCategoria = document.getElementById('modalEditarCategoria');
  const inputEditarCategoriaId = document.getElementById('edit_categoria_id');
  const inputEditarCategoriaNombre = document.getElementById('edit_categoria_nombre');
  const btnCerrarEditarCategoria = document.getElementById('btnCerrarEditarCategoria');

  const cargarCategorias = async () => {
    if (!listaCategorias) return;

    listaCategorias.innerHTML = '<p class="cargando-categorias">Cargando categorías...</p>';

    try {
      const response = await fetch(listaCategorias.dataset.url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('No se pudieron cargar las categorías.');
      listaCategorias.innerHTML = await response.text();
    } catch (error) {
      listaCategorias.innerHTML = '<p class="sin-categorias">No se pudieron cargar las categorías.</p>';
    }
  };

  if (btnAbrirModalCategoria && modalCategoria) {
    btnAbrirModalCategoria.addEventListener('click', () => {
      modalCategoria.classList.remove('hidden');
      cargarCategorias();
    });
  }

  if (btnCerrarModalCategoria && modalCategoria) { // ✅ Agregado evento de cierre
    btnCerrarModalCategoria.addEventListener('click', () => {
      modalCategoria.classList.add('hidden');
      if (formCategoria) formCategoria.reset();
    });
  }

  if (listaCategorias) {
    listaCategorias.addEventListener('click', event => {
      const botonEditar = event.target.closest('.btn-modificar-categoria');
      if (!botonEditar) return;

      if (inputEditarCategoriaId) inputEditarCategoriaId.value = botonEditar.dataset.id;
      if (inputEditarCategoriaNombre) inputEditarCategoriaNombre.value = botonEditar.dataset.nombre;
      if (modalEditarCategoria) modalEditarCategoria.classList.remove('hidden');
    });
  }

  if (btnCerrarEditarCategoria && modalEditarCategoria) {
    btnCerrarEditarCategoria.addEventListener('click', () => {
      modalEditarCategoria.classList.add('hidden');
    });
  }

  // ----------------------------------------------------
  // 3. MODAL MODIFICAR PRODUCTO
  // ----------------------------------------------------
  const modalEditar = document.getElementById('modalEditarProducto');
  const btnCerrarEditar = document.getElementById('btnCerrarModalEditar');
  const inputEditId = document.getElementById('edit_id');
  const inputEditNombre = document.getElementById('edit_nombre');
  const inputEditPrecio = document.getElementById('edit_precio');
  const selectEditCategoria = document.getElementById('edit_fk_id_categoria'); // ✅ Corregido typo

  document.querySelectorAll('.btn-modificar').forEach(boton => {
    boton.addEventListener('click', () => {
      // Obtenemos los datos desde los atributos data-* de la tarjeta
      const id = boton.getAttribute('data-id');
      const nombre = boton.getAttribute('data-nombre');
      const precio = boton.getAttribute('data-precio');
      const fkCategoria = boton.getAttribute('data-fk_id_categoria'); // ✅ Corregido typo

      // Cargamos los datos en el formulario de edición
      if (inputEditId) inputEditId.value = id;
      if (inputEditNombre) inputEditNombre.value = nombre;
      if (inputEditPrecio) inputEditPrecio.value = precio;
      if (selectEditCategoria) selectEditCategoria.value = fkCategoria;

      if (modalEditar) modalEditar.classList.remove('hidden');
    });
  });

  if (btnCerrarEditar && modalEditar) {
    btnCerrarEditar.addEventListener('click', () => {
      modalEditar.classList.add('hidden');
    });
  }

  // ----------------------------------------------------
  // 4. FILTRADO POR CATEGORÍAS
  // ----------------------------------------------------
  document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const categoriaId = boton.getAttribute('data-id');
      // Redirige pasando el ID por la URL
      window.location.href = `/Producto?fk_id_categoria=${categoriaId}`; // ✅ Corregido typo
    });
  });
});
