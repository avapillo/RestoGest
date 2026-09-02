document.addEventListener('DOMContentLoaded', () => {
  // ----------------------------------------------------
  // 1. FILTRADO POR SECCIONES
  // ----------------------------------------------------
  document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const seccionId = boton.getAttribute('data-id');
      window.location.href = `/admin/pedidos?fk_id_seccion=${seccionId}`;
    });
  });

  // ----------------------------------------------------
  // 2. MODAL REALIZAR PAGO
  // ----------------------------------------------------
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
