document.addEventListener('DOMContentLoaded', () => {

  const btnAbrirModalPedido = document.getElementById('btnAbrirModalProducto');
  const btnCerrarModalPedido = document.getElementById('btnCerrarModal');
  const modalProducto = document.getElementById('modalProducto');
  const formPedido = document.getElementById('formProducto');
  const selectSeccion = document.getElementById('fk_id_seccion');
  const selectMesa = document.getElementById('fk_id_mesa');
  const listaProductosPedido = document.getElementById('listaProductosPedido');
  let actualizarFilas = () => {};

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

  if (btnAbrirModalPedido && modalProducto) {
    btnAbrirModalPedido.addEventListener('click', () => {
      prepararNuevoPedido();
      modalProducto.classList.remove('hidden');
    });
  }


  if (btnCerrarModalPedido && modalProducto) {
    btnCerrarModalPedido.addEventListener('click', () => {
      modalProducto.classList.add('hidden');
     // if (formProducto) formProducto.reset(); // Limpia los campos al cerrar
    });
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
