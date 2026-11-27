const modalPendiente = document.getElementById('modalEditarPendiente');
    const formularioEditar = document.getElementById('formEditarPendiente');

    document.querySelectorAll('.btn-editar-pendiente').forEach(boton => {
        boton.addEventListener('click', function () {
            formularioEditar.action = this.dataset.url;

            document.getElementById('editar_nombre').value = this.dataset.nombre;
            document.getElementById('editar_monto').value = this.dataset.monto;

            modalPendiente.hidden = false;
            document.getElementById('editar_nombre').focus();
        });
    });

    function cerrarModalPendiente() {
        modalPendiente.hidden = true;
    }

    document.getElementById('btnCancelarEdicion').addEventListener('click', cerrarModalPendiente);

    modalPendiente.addEventListener('click', function (event) {
        if (event.target === modalPendiente) {
            cerrarModalPendiente();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modalPendiente.hidden) {
            cerrarModalPendiente();
        }
    });
