const altaForm = document.getElementById('formNuevoRegistro');
const nombreInput = document.getElementById('nombre');
const clienteIdInput = document.getElementById('cliente_id');
const dniInput = document.getElementById('dni_ultimos_4');
const dniGroup = document.getElementById('grupoDniCliente');
const matchesPanel = document.getElementById('coincidenciasCliente');
const statusMessage = document.getElementById('estadoAltaCliente');
const selectionMessage = document.getElementById('seleccionCliente');
const registerButton = document.getElementById('btnRegistrarCliente');

function setMessage(element, message) {
    element.textContent = message;
    element.hidden = message === '';
}

function renderMatches(clients, nombre) {
    matchesPanel.replaceChildren();
    matchesPanel.hidden = false;

    const heading = document.createElement('h3');
    heading.textContent = 'Encontramos clientes con ese nombre';
    matchesPanel.append(heading);

    const explanation = document.createElement('p');
    explanation.textContent = 'Elegí si se trata de una persona existente o si es un cliente nuevo.';
    matchesPanel.append(explanation);

    clients.forEach(client => {
        const card = document.createElement('article');
        card.className = 'pendientes-cliente-coincidente';

        const description = document.createElement('p');
        const identifier = client.dni_ultimos_4
            ? `DNI terminado en ${client.dni_ultimos_4}`
            : 'Sin DNI registrado';
        const lastRecord = client.ultimo_registro
            ? `Último movimiento: ${client.ultimo_registro.fecha_registro} · ${formatAmount(client.ultimo_registro.monto)}`
            : 'Todavía no tiene movimientos';
        description.textContent = `${client.nombre} · ${identifier} · ${lastRecord}`;

        const chooseButton = document.createElement('button');
        chooseButton.type = 'button';
        chooseButton.className = 'pendientes-btn-editar';
        chooseButton.textContent = 'Es esta persona';
        chooseButton.addEventListener('click', () => {
            clienteIdInput.value = client.id;
            dniInput.value = '';
            dniInput.required = false;
            dniGroup.hidden = true;
            setMessage(selectionMessage, `El movimiento se asociará a ${client.nombre}${client.dni_ultimos_4 ? ` (DNI terminado en ${client.dni_ultimos_4})` : ''}.`);
            setMessage(statusMessage, '');
            altaForm.dataset.readyToSubmit = 'true';
        });

        card.append(description, chooseButton);
        matchesPanel.append(card);
    });

    const createButton = document.createElement('button');
    createButton.type = 'button';
    createButton.className = 'pendientes-btn-nuevo-homonimo';
    createButton.textContent = 'Es un cliente nuevo con el mismo nombre';
    createButton.addEventListener('click', () => {
        clienteIdInput.value = '';
        dniInput.value = '';
        dniInput.required = true;
        dniGroup.hidden = false;
        setMessage(selectionMessage, 'Ingresá los últimos 4 dígitos del DNI para diferenciar a este cliente.');
        setMessage(statusMessage, '');
        altaForm.dataset.readyToSubmit = 'true';
        dniInput.focus();
    });
    matchesPanel.append(createButton);
    altaForm.dataset.checkedName = nombre;
}

function formatAmount(amount) {
    return Number(amount).toLocaleString('es-AR', {
        style: 'currency',
        currency: 'ARS',
    });
}

if (altaForm) {
    let checkingName = false;

    altaForm.addEventListener('submit', async event => {
        const nombre = nombreInput.value.trim();

        if (!nombre) {
            event.preventDefault();
            nombreInput.setCustomValidity('Ingresá el nombre del cliente.');
            nombreInput.reportValidity();
            nombreInput.setCustomValidity('');
            return;
        }

        nombreInput.value = nombre;

        if (altaForm.dataset.readyToSubmit === 'true') {
            delete altaForm.dataset.readyToSubmit;
            return;
        }

        if (checkingName) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        if (altaForm.dataset.checkedName === nombre) {
            setMessage(statusMessage, 'Elegí un cliente existente o indicá que es un cliente nuevo.');
            return;
        }

        checkingName = true;
        registerButton.disabled = true;
        setMessage(statusMessage, 'Buscando coincidencias...');
        matchesPanel.hidden = true;

        try {
            const url = new URL(altaForm.dataset.searchUrl, window.location.origin);
            url.searchParams.set('nombre', nombre);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No se pudo consultar la lista de clientes.');
            }

            const data = await response.json();

            if (data.coincidencias.length > 0) {
                setMessage(statusMessage, 'Elegí si es un cliente existente o si querés registrar un homónimo.');
                renderMatches(data.coincidencias, nombre);
                return;
            }

            matchesPanel.replaceChildren();
            matchesPanel.hidden = true;
            clienteIdInput.value = '';
            dniInput.value = '';
            dniInput.required = false;
            dniGroup.hidden = true;
            setMessage(selectionMessage, '');
            setMessage(statusMessage, '');
            altaForm.dataset.readyToSubmit = 'true';
            registerButton.disabled = false;
            altaForm.requestSubmit(registerButton);
        } catch (error) {
            setMessage(statusMessage, error.message || 'Ocurrió un error al buscar el cliente.');
        } finally {
            checkingName = false;
            registerButton.disabled = false;
        }
    });

    nombreInput.addEventListener('input', () => {
        delete altaForm.dataset.checkedName;
        delete altaForm.dataset.readyToSubmit;
        matchesPanel.hidden = true;
        clienteIdInput.value = '';
        dniInput.value = '';
        dniInput.required = false;
        dniGroup.hidden = true;
        setMessage(selectionMessage, '');
        setMessage(statusMessage, '');
    });
}

const searchForm = document.getElementById('formBuscarClientes');
const searchInput = document.getElementById('busquedaCliente');
const searchResults = document.getElementById('resultadosBusqueda');

function appendCell(row, value, className = '') {
    const cell = document.createElement('td');
    cell.textContent = value;
    if (className) {
        cell.className = className;
    }
    row.append(cell);
}

if (searchForm) {
    searchForm.addEventListener('submit', async event => {
        event.preventDefault();
        const query = searchInput.value.trim();
        searchResults.replaceChildren();

        if (!query) {
            searchResults.textContent = 'Ingresá un nombre o los últimos 4 dígitos del DNI.';
            return;
        }

        searchResults.textContent = 'Buscando...';

        try {
            const url = new URL(searchForm.dataset.searchUrl, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar los resultados.');
            }

            const { resultados } = await response.json();
            searchResults.replaceChildren();

            if (resultados.length === 0) {
                searchResults.textContent = 'No se encontraron clientes ni movimientos.';
                return;
            }

            const table = document.createElement('table');
            table.className = 'pendientes-tabla';
            const header = document.createElement('tr');
            ['Fecha del registro', 'Cliente', 'DNI (últimos 4)', 'Monto'].forEach(label => {
                const cell = document.createElement('th');
                cell.scope = 'col';
                cell.textContent = label;
                header.append(cell);
            });
            const head = document.createElement('thead');
            head.append(header);
            const body = document.createElement('tbody');

            resultados.forEach(result => {
                const row = document.createElement('tr');
                appendCell(row, result.fecha_registro || '—');
                appendCell(row, result.nombre);
                appendCell(row, result.dni_ultimos_4 || '—');
                appendCell(row, formatAmount(result.monto), 'pendientes-monto');
                body.append(row);
            });

            table.append(head, body);
            const wrapper = document.createElement('div');
            wrapper.className = 'pendientes-tabla-envoltura';
            wrapper.append(table);
            searchResults.append(wrapper);
        } catch (error) {
            searchResults.textContent = error.message || 'Ocurrió un error al buscar.';
        }
    });
}

const modalPendiente = document.getElementById('modalEditarPendiente');
const formularioEditar = document.getElementById('formEditarPendiente');

document.querySelectorAll('.btn-editar-pendiente').forEach(button => {
    button.addEventListener('click', function () {
        formularioEditar.action = this.dataset.url;
        document.getElementById('editar_nombre').value = this.dataset.nombre;
        document.getElementById('editar_monto').value = this.dataset.monto;
        modalPendiente.hidden = false;
        document.getElementById('editar_monto').focus();
    });
});

function cerrarModalPendiente() {
    modalPendiente.hidden = true;
}

document.getElementById('btnCancelarEdicion').addEventListener('click', cerrarModalPendiente);

modalPendiente.addEventListener('click', event => {
    if (event.target === modalPendiente) {
        cerrarModalPendiente();
    }
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !modalPendiente.hidden) {
        cerrarModalPendiente();
    }
});
