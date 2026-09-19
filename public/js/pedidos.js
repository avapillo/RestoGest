document.addEventListener('DOMContentLoaded', () => {
  // Filtro por secciones
  document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const seccionId = boton.getAttribute('data-id');
      window.location.href = `/Mesas?fk_id_seccion=${seccionId}`;
    });
  });
});
