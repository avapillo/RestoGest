document.addEventListener('DOMContentLoaded', () => {

  const btnAbrirModalPedido = document.getElementById('btnAbrirModalProducto');
  const btnCerrarModalPedido = document.getElementById('btnCerrarModal');
  const modalProducto = document.getElementById('modalProducto');
  const selectSeccion = document.getElementById('fk_id_seccion');
  const selectMesa = document.getElementById('fk_id_mesa');

  if (btnAbrirModalPedido && modalProducto) {
    btnAbrirModalPedido.addEventListener('click', () => {
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

  // Filtro por secciones
  document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const seccionId = boton.getAttribute('data-id');
      window.location.href = `/Mesas?fk_id_seccion=${seccionId}`;
    });
  });
});
