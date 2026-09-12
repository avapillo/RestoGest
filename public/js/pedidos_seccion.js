document.addEventListener('DOMContentLoaded', () => {
// Filtrado de secciones vía parámetros GET
document.querySelectorAll('.btn-filtro').forEach(boton => {
  boton.addEventListener('click', () => {
    const seccionId = boton.getAttribute('data-id');
    const baseUrl = window.rutaMesas || '/Mesas';

    if (seccionId && seccionId !== 'todas') {
      window.location.href = `${baseUrl}?fk_id_seccion=${seccionId}`;
    } else {
      window.location.href = baseUrl;
    }
  });
});
  // 2. Modal de Modificación de Pedido (Grilla de Productos)
  const modalEditar = document.getElementById('modalEditarPedido');
  const btnCerrarModalEditar = document.getElementById('btnCerrarModalEditar');
  const lblMesaEditar = document.getElementById('lblMesaEditar');
  const editIdPedido = document.getElementById('edit_id_pedido');
  const contenedorDetalles = document.getElementById('contenedorDetallesEditar');

  document.querySelectorAll('.btnAbrirModalEditar').forEach(boton => {
    boton.addEventListener('click', () => {
      const idPedido = boton.getAttribute('data-id_pedido');
      const numeroMesa = boton.getAttribute('data-numero_mesa');
      const detalles = JSON.parse(boton.getAttribute('data-detalles') || '[]');

      if (lblMesaEditar) lblMesaEditar.textContent = numeroMesa;
      if (editIdPedido) editIdPedido.value = idPedido;

      if (contenedorDetalles) {
        contenedorDetalles.innerHTML = '';
        detalles.forEach((item) => {
          const nombreItem = item.producto ? item.producto.nombre : (item.combo ? item.combo.combo : 'Ítem');
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${nombreItem}</td>
            <td>
              <input type="number" name="detalles[${item.id_detalle}][cantidad]" value="${item.cantidad}" min="1" class="input-cantidad-grilla">
            </td>
            <td style="text-align: center;">
              <button type="button" class="btn-eliminar-item" onclick="this.closest('tr').remove()">🗑️</button>
            </td>
          `;
          contenedorDetalles.appendChild(tr);
        });
      }

      if (modalEditar) modalEditar.classList.remove('hidden');
    });
  });

  if (btnCerrarModalEditar && modalEditar) {
    btnCerrarModalEditar.addEventListener('click', () => {
      modalEditar.classList.add('hidden');
    });
  }

  // 3. Modal de Pago
  const modalPago = document.getElementById('modalPago');
  const btnCerrarModalPago = document.getElementById('btnCerrarModalPago');
  const lblNumeroMesa = document.getElementById('lblNumeroMesa');
  const inputPagoIdPedido = document.getElementById('pago_id_pedido');
  const inputPagoMontoTotal = document.getElementById('pago_monto_total');

  document.querySelectorAll('.btnAbrirModalPago').forEach(boton => {
    boton.addEventListener('click', () => {
      const idPedido = boton.getAttribute('data-id_pedido');
      const numeroMesa = boton.getAttribute('data-numero_mesa');
      const monto = boton.getAttribute('data-monto');

      if (lblNumeroMesa) lblNumeroMesa.textContent = numeroMesa;
      if (inputPagoIdPedido) inputPagoIdPedido.value = idPedido;
      if (inputPagoMontoTotal) inputPagoMontoTotal.value = `$ ${parseFloat(monto).toFixed(2)}`;

      if (modalPago) modalPago.classList.remove('hidden');
    });
  });

  if (btnCerrarModalPago && modalPago) {
    btnCerrarModalPago.addEventListener('click', () => {
      modalPago.classList.add('hidden');
    });
  }
});
