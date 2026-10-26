document.addEventListener('DOMContentLoaded', () => {

  const botonesAbrirModalPedido = document.querySelectorAll('.btn-tomar-pedido');
  const btnCerrarModalPedido = document.getElementById('btnCerrarModal');
  const modalProducto = document.getElementById('modalProducto');
  const btnAbrirModalMesa = document.getElementById('btnAbrirModalMesa');
  const btnCerrarModalMesa = document.getElementById('btnCerrarModalMesa');
  const modalMesa = document.getElementById('modalMesa');
  const formPedido = document.getElementById('formProducto');
  const selectSeccion = document.getElementById('fk_id_seccion');
  const selectMesa = document.getElementById('fk_id_mesa');
  const listaProductosPedido = document.getElementById('listaProductosPedido');
  let actualizarFilas = () => {};

  const btnAbrirModalSecciones = document.getElementById('btnAbrirModalSecciones');
  const btnCerrarModalSecciones = document.getElementById('btnCerrarModalSecciones');
  const modalSecciones = document.getElementById('modalSecciones');
  const listaSecciones = document.getElementById('listaSecciones');
  const modalEditarSeccion = document.getElementById('modalEditarSeccion');
  const btnCerrarEditarSeccion = document.getElementById('btnCerrarEditarSeccion');
  const inputEditarSeccionId = document.getElementById('edit_seccion_id');
  const inputEditarSeccionNombre = document.getElementById('edit_seccion_nombre');
  const btnAbrirModalCombos = document.getElementById('btnAbrirModalCombos');
  const btnCerrarModalCombos = document.getElementById('btnCerrarModalCombos');
  const modalCombos = document.getElementById('modalCombos');
  const listaCombos = document.getElementById('listaCombos');
  const modalEditarCombo = document.getElementById('modalEditarCombo');
  const btnCerrarEditarCombo = document.getElementById('btnCerrarEditarCombo');
  const inputEditarComboId = document.getElementById('edit_combo_id');
  const inputEditarComboNombre = document.getElementById('edit_combo_nombre');

  const cargarSecciones = async () => {
    if (!listaSecciones) return;

    listaSecciones.innerHTML = '<p class="cargando-secciones">Cargando secciones...</p>';

    try {
      const response = await fetch(listaSecciones.dataset.url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('No se pudieron cargar las secciones.');
      listaSecciones.innerHTML = await response.text();
    } catch (error) {
      listaSecciones.innerHTML = '<p class="sin-secciones">No se pudieron cargar las secciones.</p>';
    }
  };

  if (btnAbrirModalSecciones && modalSecciones) {
    btnAbrirModalSecciones.addEventListener('click', () => {
      modalSecciones.classList.remove('hidden');
      cargarSecciones();
    });
  }

  if (btnCerrarModalSecciones && modalSecciones) {
    btnCerrarModalSecciones.addEventListener('click', () => modalSecciones.classList.add('hidden'));
  }

  if (listaSecciones) {
    listaSecciones.addEventListener('click', event => {
      const botonEditar = event.target.closest('.btn-modificar-seccion');
      if (!botonEditar) return;

      if (inputEditarSeccionId) inputEditarSeccionId.value = botonEditar.dataset.id;
      if (inputEditarSeccionNombre) inputEditarSeccionNombre.value = botonEditar.dataset.nombre;
      if (modalEditarSeccion) modalEditarSeccion.classList.remove('hidden');
    });
  }

  if (btnCerrarEditarSeccion && modalEditarSeccion) {
    btnCerrarEditarSeccion.addEventListener('click', () => modalEditarSeccion.classList.add('hidden'));
  }

  const cargarCombos = async () => {
    if (!listaCombos) return;

    listaCombos.innerHTML = '<p class="cargando-secciones">Cargando combos...</p>';

    try {
      const response = await fetch(listaCombos.dataset.url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('No se pudieron cargar los combos.');
      listaCombos.innerHTML = await response.text();
    } catch (error) {
      listaCombos.innerHTML = '<p class="sin-secciones">No se pudieron cargar los combos.</p>';
    }
  };

  if (btnAbrirModalCombos && modalCombos) {
    btnAbrirModalCombos.addEventListener('click', () => {
      modalCombos.classList.remove('hidden');
      cargarCombos();
    });
  }

  if (btnCerrarModalCombos && modalCombos) {
    btnCerrarModalCombos.addEventListener('click', () => modalCombos.classList.add('hidden'));
  }

  if (listaCombos) {
    listaCombos.addEventListener('click', event => {
      const botonEditar = event.target.closest('.btn-modificar-combo');
      if (!botonEditar) return;

      if (inputEditarComboId) inputEditarComboId.value = botonEditar.dataset.id;
      if (inputEditarComboNombre) inputEditarComboNombre.value = botonEditar.dataset.nombre;
      if (modalEditarCombo) modalEditarCombo.classList.remove('hidden');
    });
  }

  if (btnCerrarEditarCombo && modalEditarCombo) {
    btnCerrarEditarCombo.addEventListener('click', () => modalEditarCombo.classList.add('hidden'));
  }

  const limpiarFilasPedido = () => {
    if (!listaProductosPedido) return;

    const filas = [...listaProductosPedido.querySelectorAll('.fila-item-producto')];
    filas.slice(1).forEach(fila => fila.remove());
    const fila = filas[0];
    if (!fila) return;

    fila.querySelector('.selector-categoria').value = '';
    fila.querySelector('.selector-producto').value = '';
    fila.querySelector('.selector-producto').disabled = true;
    fila.querySelector('input[type="number"]').value = '1';
    fila.querySelector('input[type="text"]').value = '';
    [...fila.querySelector('.selector-producto').options].forEach(option => {
      option.hidden = false;
      option.disabled = false;
    });
    actualizarFilas();
  };

  const prepararNuevoPedido = () => {
    if (!formPedido) return;

    formPedido.reset();
    limpiarFilasPedido();
    formPedido.action = formPedido.dataset.createUrl;
    modalProducto.querySelector('h3').textContent = 'Agregar Pedido';
    formPedido.querySelector('button[type="submit"]').textContent = 'Registrar Pedido';
  };

  if (modalProducto) {
    botonesAbrirModalPedido.forEach(boton => boton.addEventListener('click', () => {
      prepararNuevoPedido();
      selectSeccion.value = boton.dataset.seccionId;
      selectMesa.value = boton.dataset.mesaId;
      modalProducto.classList.remove('hidden');
    }));
  }


  if (btnCerrarModalPedido && modalProducto) {
    btnCerrarModalPedido.addEventListener('click', () => {
      modalProducto.classList.add('hidden');
     // if (formProducto) formProducto.reset(); // Limpia los campos al cerrar
    });
  }

  if (btnAbrirModalMesa && modalMesa) {
    btnAbrirModalMesa.addEventListener('click', () => modalMesa.classList.remove('hidden'));
  }

  if (btnCerrarModalMesa && modalMesa) {
    btnCerrarModalMesa.addEventListener('click', () => modalMesa.classList.add('hidden'));
  }

  if (selectSeccion && selectMesa) {
    selectSeccion.addEventListener('change', async () => {
      const seccionId = selectSeccion.value;
      selectMesa.replaceChildren(new Option('Seleccionar Mesa', ''));
      selectMesa.disabled = !seccionId;

      if (!seccionId) return;

      const token = document.querySelector('#formProducto input[name="_token"]').value;
      const response = await fetch('/Mesas/Pedido', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ fk_id_seccion: seccionId }),
      });

      if (!response.ok) return;

      const mesas = await response.json();
      mesas.forEach(mesa => {
        selectMesa.add(new Option(`Mesa N° ${mesa.numero_mesa}`, mesa.id_mesa));
      });
      selectMesa.disabled = mesas.length === 0;
    });
  }

  if (listaProductosPedido) {
    actualizarFilas = () => {
      const filas = [...listaProductosPedido.querySelectorAll('.fila-item-producto')];

      filas.forEach((fila, indice) => {
        fila.querySelectorAll('[name]').forEach(campo => {
          campo.name = campo.name.replace(/items\[\d+\]/, `items[${indice}]`);
        });
        fila.querySelectorAll('[id]').forEach(campo => {
          campo.id = campo.id.replace(/-item-\d+$/, `-item-${indice}`);
        });
        fila.querySelectorAll('label[for]').forEach(label => {
          label.htmlFor = label.htmlFor.replace(/-item-\d+$/, `-item-${indice}`);
        });
        fila.querySelector('[data-accion="quitar"]').disabled = filas.length === 1;
      });
    };

    listaProductosPedido.addEventListener('change', event => {
      if (!event.target.matches('.selector-categoria')) return;

      const fila = event.target.closest('.fila-item-producto');
      const selectProducto = fila.querySelector('.selector-producto');
      const categoriaId = event.target.value;

      selectProducto.value = '';
      selectProducto.disabled = !categoriaId;
      [...selectProducto.options].forEach(option => {
        if (!option.value) return;
        option.hidden = option.dataset.categoria !== categoriaId;
        option.disabled = option.dataset.categoria !== categoriaId;
      });
    });

    listaProductosPedido.addEventListener('click', event => {
      const boton = event.target.closest('[data-accion]');
      if (!boton) return;

      const fila = boton.closest('.fila-item-producto');
      if (boton.dataset.accion === 'agregar') {
        const nuevaFila = fila.cloneNode(true);
        nuevaFila.querySelector('.selector-categoria').value = '';
        nuevaFila.querySelector('.selector-producto').value = '';
        nuevaFila.querySelector('.selector-producto').disabled = true;
        nuevaFila.querySelector('input[type="number"]').value = '1';
        nuevaFila.querySelector('input[type="text"]').value = '';
        [...nuevaFila.querySelector('.selector-producto').options].forEach(option => {
          option.hidden = false;
          option.disabled = false;
        });
        fila.after(nuevaFila);
      } else if (boton.dataset.accion === 'quitar' && listaProductosPedido.children.length > 1) {
        fila.remove();
      }

      actualizarFilas();
    });

    actualizarFilas();
  }

  document.querySelectorAll('.btn-pedido-modificar:not(:disabled)').forEach(boton => {
    boton.addEventListener('click', () => {
      prepararNuevoPedido();
      formPedido.action = boton.dataset.updateUrl;
      selectSeccion.value = boton.dataset.seccionId;
      selectMesa.value = boton.dataset.mesaId;
      document.getElementById('es_para_llevar').value = boton.dataset.paraLlevar;

      const items = JSON.parse(boton.dataset.items);
      const filaBase = listaProductosPedido.querySelector('.fila-item-producto');

      items.forEach((item, indice) => {
        const fila = indice === 0 ? filaBase : filaBase.cloneNode(true);
        if (indice > 0) listaProductosPedido.append(fila);

        const categoria = fila.querySelector('.selector-categoria');
        const producto = fila.querySelector('.selector-producto');
        categoria.value = item.fk_id_categoria;
        categoria.dispatchEvent(new Event('change', { bubbles: true }));
        producto.value = item.fk_id_producto;
        fila.querySelector('input[type="number"]').value = item.cantidad;
        fila.querySelector('input[type="text"]').value = item.descripcion || '';
      });

      actualizarFilas();
      modalProducto.querySelector('h3').textContent = 'Modificar Pedido';
      formPedido.querySelector('button[type="submit"]').textContent = 'Guardar cambios';
      modalProducto.classList.remove('hidden');
    });
  });

  const modalCobrar = document.getElementById('modalCobrar');
  const formCobrar = document.getElementById('formCobrar');
  const cerrarModalCobro = document.getElementById('btnCerrarModalCobro');

  document.querySelectorAll('.btn-pedido-cobrar').forEach(boton => {
    boton.addEventListener('click', () => {
      formCobrar.reset();
      document.getElementById('pagoIdPedido').value = boton.dataset.orderId;
      document.getElementById('totalCobroPedido').textContent = `$${Number(boton.dataset.total).toLocaleString('es-CL')}`;
      modalCobrar.classList.remove('hidden');
    });
  });

  if (cerrarModalCobro && modalCobrar) {
    cerrarModalCobro.addEventListener('click', () => modalCobrar.classList.add('hidden'));
  }

  // Filtro por secciones
  document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const seccionId = boton.getAttribute('data-id');
      window.location.href = `/Mesas?fk_id_seccion=${seccionId}`;
    });
  });
});
