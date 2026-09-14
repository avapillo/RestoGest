document.addEventListener('DOMContentLoaded', () => {
    const btnAbrirModalUsuario = document.getElementById('btnAbrirNuevoUsuario');
    const btnCerrarModalUsuario = document.getElementById('btnCerrarModal');
    const modalUsuario = document.getElementById('modalUsuario');
    const formUsuario = document.getElementById('formUsuario');

     if (btnAbrirModalUsuario && modalUsuario) {
    btnAbrirModalUsuario.addEventListener('click', () => {
      modalUsuario.classList.remove('hidden');
    });
  }

  if (btnCerrarModalUsuario && modalUsuario) {
    btnCerrarModalUsuario.addEventListener('click', () => {
      modalUsuario.classList.add('hidden');
      if (formUsuario) formUsuario.reset(); // Limpia los campos al cerrar
    });
  }

  // Modifcación de Usuario

   const modalEditar = document.getElementById('modalEditarUsuario');
  const btnCerrarEditar = document.getElementById('btnCerrarModalEditar');
  const inputEditId = document.getElementById('edit_id');
  const inputEditNombre = document.getElementById('edit_nombre');
  const inputEditContraseña = document.getElementById('edit_contraseña');
  const selectEditCategoria = document.getElementById('edit_fk_id_categoria'); // ✅ Corregido typo

  document.querySelectorAll('.btn-modificar').forEach(boton => {
    boton.addEventListener('click', () => {
      // Obtenemos los datos desde los atributos data-* de la tarjeta
      const id = boton.getAttribute('data-id');
      const nombre = boton.getAttribute('data-nombre');
      const contraseña = boton.getAttribute('data-contrasenia');
      const fkCategoria = boton.getAttribute('data-fk_id_categoria'); // ✅ Corregido typo

      // Cargamos los datos en el formulario de edición
      if (inputEditId) inputEditId.value = id;
      if (inputEditNombre) inputEditNombre.value = nombre;
      if (inputEditContraseña) inputEditContraseña.value = contraseña;
      if (selectEditCategoria) selectEditCategoria.value = fkCategoria;

      if (modalEditar) modalEditar.classList.remove('hidden');
    });
  });

  if (btnCerrarEditar && modalEditar) {
    btnCerrarEditar.addEventListener('click', () => {
      modalEditar.classList.add('hidden');
    });
  }
});
