document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.btn-filtro').forEach(boton => {
    boton.addEventListener('click', () => {
      const seccionID = boton.getAttribute('data-id');

      // Redirige pasando únicamente el ID de la sección elegida
      const urlActual = window.location.pathname;
      window.location.href = `${urlActual}?id_seccion=${seccionID}`;
    });

});
