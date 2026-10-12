document.addEventListener('DOMContentLoaded', () => {
  // ==========================================
  // 1. MODAL NUEVO USUARIO
  // ==========================================
  const btnAbrirModalUsuario = document.getElementById('btnAbrirNuevoUsuario'); // Botón para abrir el modal principal
  const btnCerrarModalUsuario = document.getElementById('btnCerrarModal');
  const modalUsuario = document.getElementById('modalUsuario');
  const formUsuario = document.getElementById('formUsuario');

  if (btnAbrirModalUsuario && modalUsuario) {
    btnAbrirModalUsuario.addEventListener('click', () => {
      modalUsuario.classList.remove('hidden');
    });
  }

  if (btnComboBox && modalComboBox){
    btnComboBox.addEventListener('click', () => {
      modalComboBox.classList.remove('hidden');
    });
  }

  if (btnCerrarModalUsuario && modalUsuario) {
    btnCerrarModalUsuario.addEventListener('click', () => {
      modalUsuario.classList.add('hidden');
      if (formUsuario) formUsuario.reset(); // Limpia los campos al cerrar

      // Reinicia el contenedor de roles a un solo select inicial
      const contenedorNuevo = document.getElementById('contenedorCombosNuevo');
      if (contenedorNuevo) {
        const primerCombo = contenedorNuevo.querySelector('.combo-rol-item');
        contenedorNuevo.innerHTML = '';
        if (primerCombo) contenedorNuevo.appendChild(primerCombo);
      }
    });
  }

  // ==========================================
  // FUNCIÓN AUXILIAR PARA CREAR Y ELIMINAR SELECTS
  // ==========================================
  function crearComboRol(valorSeleccionado = '') {
    // Clonamos las opciones del primer select que ya existe en el HTML
    const selectBase = document.querySelector('select[name="roles[]"]') || document.getElementById('id_rol_usuario');

    const div = document.createElement('div');
    div.className = 'combo-rol-item';
    div.style.display = 'flex';
    div.style.gap = '8px';
    div.style.marginBottom = '8px';

    const select = document.createElement('select');
    select.name = 'roles[]';
    select.className = 'select-categoria';
    select.required = true;

    if (selectBase) {
      select.innerHTML = selectBase.innerHTML;
    }

    if (valorSeleccionado) {
      select.value = valorSeleccionado;
    }

    // Botón con ❌ para eliminar este rol de la lista
    const btnEliminar = document.createElement('button');
    btnEliminar.type = 'button';
    btnEliminar.className = 'btn-accion btn-eliminar';
    btnEliminar.innerText = '❌';
    btnEliminar.title = 'Quitar este rol';
    btnEliminar.onclick = () => div.remove();

    div.appendChild(select);
    div.appendChild(btnEliminar);

    return div;
  }

  // Botón "➕ Agregar otro rol" en el Modal de Nuevo Usuario
  const btnAgregarRolNuevo = document.getElementById('btnAgregarComboNuevo');
  const contenedorNuevo = document.getElementById('contenedorCombosNuevo');

  if (btnAgregarRolNuevo && contenedorNuevo) {
    btnAgregarRolNuevo.addEventListener('click', () => {
      contenedorNuevo.appendChild(crearComboRol());
    });
  }

  // ==========================================
  // 2. MODAL MODIFICAR USUARIO
  // ==========================================
  const modalEditar = document.getElementById('modalEditarUsuario');
  const btnCerrarEditar = document.getElementById('btnCerrarModalEditar');
  const inputEditId = document.getElementById('edit_id');
  const inputEditNombre = document.getElementById('edit_nombre');
  const inputEditGmail = document.getElementById('edit_gmail');
  const contenedorEditar = document.getElementById('contenedorCombosEditar');
  const btnAgregarRolEditar = document.getElementById('btnAgregarComboEditar');

  // Botón "➕ Agregar otro rol" en el Modal de Edición
  if (btnAgregarRolEditar && contenedorEditar) {
    btnAgregarRolEditar.addEventListener('click', () => {
      contenedorEditar.appendChild(crearComboRol());
    });
  }

  // Evento al presionar "✏️ Modificar" en las tarjetas
  document.querySelectorAll('.btn-modificar').forEach(boton => {
    boton.addEventListener('click', () => {
      const id = boton.getAttribute('data-id');
      const nombre = boton.getAttribute('data-nombre');
      const gmail = boton.getAttribute('data-gmail');

      // Leemos el array de IDs de roles desde el atributo data-roles='@json(...)'
      const rolesData = boton.getAttribute('data-roles');
      let rolesArray = [];

      try {
        rolesArray = rolesData ? JSON.parse(rolesData) : [];
      } catch (e) {
        rolesArray = [];
      }

      // Asignación de datos básicos
      if (inputEditId) inputEditId.value = id;
      if (inputEditNombre) inputEditNombre.value = nombre;
      if (inputEditGmail) inputEditGmail.value = gmail;

      // Cargar comboboxes por cada rol en el modal de edición
      if (contenedorEditar) {
        contenedorEditar.innerHTML = ''; // Limpiamos combos previos

        if (rolesArray.length > 0) {
          // Crea un combobox por cada rol asignado
          rolesArray.forEach(idRol => {
            contenedorEditar.appendChild(crearComboRol(idRol));
          });
        } else {
          // Si no tiene roles, crea un combobox vacío
          contenedorEditar.appendChild(crearComboRol());
        }
      }

      if (modalEditar) modalEditar.classList.remove('hidden');
    });
  });

  if (btnCerrarEditar && modalEditar) {
    btnCerrarEditar.addEventListener('click', () => {
      modalEditar.classList.add('hidden');
    });
  }
});
