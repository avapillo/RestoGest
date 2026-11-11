// ============================================================
// CONFIGURACIÓN
// ============================================================

const DIRECCION_API = '/api/historial-caja';
const API_ESTADO = '/api/caja/estado';
const API_ABRIR = '/api/caja/abrir';
const API_PAGO = '/api/caja/pago';
const API_CERRAR = '/api/caja/cerrar';

let cajaAbierta = false;
let acumuladoPagos = 0;
let mensajeCaja = 'Cargando estado de caja...';
let gastosPendientesCierre = [];


// ============================================================
// INICIALIZACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    cargarEstadoCaja();

    solicitarDatosServidor();

    inicializarControlesCaja();

    inicializarRegistroGastos();

});


// ============================================================
// ELEMENTOS
// ============================================================

function obtenerElementosCaja() {

    return {
        btnAbrir: document.getElementById('btn-abrir-caja'),
        btnCerrar: document.getElementById('btn-cerrar-caja'),
        visorTotal: document.getElementById('total-pagos'),
        estado: document.getElementById('estado-api')
    };

}


// ============================================================
// CARGAR ESTADO REAL DE LA CAJA
// ============================================================

function cargarEstadoCaja() {

    fetch(API_ESTADO)

        .then(respuesta => {

            if (!respuesta.ok) {
                throw new Error('No se pudo obtener el estado de la caja');
            }

            return respuesta.json();

        })

        .then(datos => {

            cajaAbierta = datos.abierta;
            acumuladoPagos = parseFloat(datos.monto_actual || 0);
            const campoMontoInicial = document.getElementById('monto-inicial');
            if (campoMontoInicial) {
                campoMontoInicial.value = Number(datos.monto_inicial || 0).toFixed(2);
            }
            mensajeCaja = datos.mensaje || (cajaAbierta ? 'Caja abierta.' : 'La caja está cerrada.');

            actualizarInterfazCaja();

        })

        .catch(error => {

            console.error('Error al obtener estado de caja:', error);
            mensajeCaja = 'No se pudo consultar el estado de la caja.';
            actualizarInterfazCaja();
            document.getElementById('estado-api')?.classList.add('estado-error');

        });

}


// ============================================================
// BOTONES
// ============================================================

function inicializarControlesCaja() {

    const { btnAbrir, btnCerrar } = obtenerElementosCaja();
    const modalMensajeCierre = document.getElementById('modal-mensaje-cierre');
    const btnCerrarMensaje = document.getElementById('btn-cerrar-mensaje-cierre');

    if (!btnAbrir || !btnCerrar) {

        console.error('No se encontraron los controles de caja.');

        return;

    }


    // ABRIR CAJA
    btnAbrir.addEventListener('click', abrirCaja);


    // CERRAR CAJA
    btnCerrar.addEventListener('click', cerrarCaja);

    btnCerrarMensaje?.addEventListener('click', () => {
        modalMensajeCierre.hidden = true;
    });

    modalMensajeCierre?.addEventListener('click', evento => {
        if (evento.target === modalMensajeCierre) {
            modalMensajeCierre.hidden = true;
        }
    });

}


// ============================================================
// ABRIR CAJA
// ============================================================

function abrirCaja() {

    if (cajaAbierta) {
        return;
    }

    const montoInicial = Number(document.getElementById('monto-inicial')?.value);
    if (!Number.isFinite(montoInicial) || montoInicial < 0) {
        alert('Ingrese un monto inicial válido.');
        return;
    }

    fetch(API_ABRIR, {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCSRF()
        },
        body: JSON.stringify({
            monto_inicial: montoInicial
        })

    })

    .then(respuesta => {

        if (!respuesta.ok) {
            throw new Error('No se pudo abrir la caja');
        }

        return respuesta.json();

    })

    .then(datos => {

        cajaAbierta = datos.abierta;
        acumuladoPagos = parseFloat(datos.monto_actual || 0);
        const campoMontoInicial = document.getElementById('monto-inicial');
        if (campoMontoInicial) {
            campoMontoInicial.value = Number(datos.monto_inicial || 0).toFixed(2);
        }
        mensajeCaja = datos.mensaje || 'Caja abierta.';

        actualizarInterfazCaja();

        alert('Caja abierta correctamente.');

    })

    .catch(error => {

        console.error(error);

        alert('No se pudo abrir la caja.');

    });

}


// ============================================================
// CERRAR CAJA
// ============================================================

function cerrarCaja() {

    if (!cajaAbierta) {
        return;
    }


    const monto = acumuladoPagos.toFixed(2);


    const confirmar = confirm(
        `¿Desea cerrar la caja con un total de $${formatearMonto(monto)}?`
    );


    if (!confirmar) {
        return;
    }


    fetch(API_CERRAR, {

        method: 'POST',

// Envia los gastos pendientes registrados
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCSRF()
        },
        body: JSON.stringify({
            gastos: gastosPendientesCierre.map(gasto => ({
                producto: gasto.product.trim(),
                monto: Number(gasto.amount)
            }))
        })

    })

    .then(async respuesta => {
        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.mensaje || 'No se pudo cerrar la caja.');
        }

        return datos;

    })

    .then(datos => {

        cajaAbierta = false;
        acumuladoPagos = 0;
        const campoMontoInicial = document.getElementById('monto-inicial');
        if (campoMontoInicial) {
            campoMontoInicial.value = '0.00';
        }
        mensajeCaja = 'La caja está cerrada.';
        window.dispatchEvent(new Event('caja:gastos-guardados'));

        actualizarInterfazCaja();

        solicitarDatosServidor();

        alert('Caja cerrada correctamente.');

    })

    .catch(error => {

        console.error(error);

        const modal = document.getElementById('modal-mensaje-cierre');
        const texto = document.getElementById('texto-mensaje-cierre');

        if (modal && texto) {
            texto.textContent = error.message;
            modal.hidden = false;
            document.getElementById('btn-cerrar-mensaje-cierre')?.focus();
        } else {
            alert(error.message);
        }

    });

}


// ============================================================
// REGISTRAR PAGO
// ============================================================

window.registrarNuevoPago = function(monto) {

    if (!cajaAbierta) {

        alert('Debe abrir la caja primero para poder registrar un pago.');

        return;

    }


    const montoNumerico = parseFloat(monto);


    if (isNaN(montoNumerico) || montoNumerico <= 0) {

        console.error('Monto inválido:', monto);

        return;

    }


    fetch(API_PAGO, {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCSRF()
        },

        body: JSON.stringify({
            monto: montoNumerico
        })

    })

    .then(respuesta => {

        if (!respuesta.ok) {
            throw new Error('No se pudo registrar el pago');
        }

        return respuesta.json();

    })

    .then(datos => {

        acumuladoPagos = parseFloat(datos.monto_actual || 0);

        actualizarInterfazCaja();

    })

    .catch(error => {

        console.error('Error al registrar pago:', error);

    });

};


// ============================================================
// ACTUALIZAR INTERFAZ
// ============================================================

function actualizarInterfazCaja() {

    const {
        visorTotal,
        estado
    } = obtenerElementosCaja();


    if (visorTotal) {

        visorTotal.textContent = formatearMonto(acumuladoPagos);

    }

    if (estado) {
        estado.textContent = mensajeCaja;
        estado.hidden = false;
        estado.classList.remove('estado-error');
    }

}


// ============================================================
// FORMATO DEL MONTO
// ============================================================

function formatearMonto(monto) {

    return parseFloat(monto || 0).toLocaleString('es-AR', {

        minimumFractionDigits: 2,

        maximumFractionDigits: 2

    });

}


// ============================================================
// TOKEN CSRF
// ============================================================

function obtenerTokenCSRF() {

    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute('content') : '';

}


// ============================================================
// REGISTRO DE GASTOS
// ============================================================

function inicializarRegistroGastos() {

    const botonAbrir = document.getElementById('btn-abrir-gastos');
    const modal = document.getElementById('modal-gastos');
    const formulario = document.getElementById('formulario-gastos');
    const filas = document.getElementById('filas-formulario-gastos');
    const plantilla = document.getElementById('plantilla-fila-gasto');
    const botonAgregar = document.getElementById('btn-agregar-fila-gasto');
    const botonCerrar = document.getElementById('btn-cerrar-gastos');
    const botonCancelar = document.getElementById('btn-cancelar-gastos');
    const total = document.getElementById('total-gastos');
    const lista = document.getElementById('lista-gastos');
    const estado = document.getElementById('estado-registro-gastos');
    const claveAlmacenamiento = 'restogest.gastos';
    const formatoMoneda = new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    if (!botonAbrir || !modal || !formulario || !filas || !plantilla || !total || !lista || !estado) {
        return;
    }

    let gastos = cargarGastos();
    gastosPendientesCierre = gastos;

    function cargarGastos() {
        try {
            const guardados = JSON.parse(localStorage.getItem(claveAlmacenamiento) || '[]');
            return Array.isArray(guardados)
                ? guardados.filter(gasto => (
                    gasto
                    && typeof gasto.product === 'string'
                    && Number.isFinite(Number(gasto.amount))
                    && Number(gasto.amount) > 0
                ))
                : [];
        } catch {
            return [];
        }
    }

    function guardarGastos() {
        try {
            localStorage.setItem(claveAlmacenamiento, JSON.stringify(gastos));
            gastosPendientesCierre = gastos;
            return true;
        } catch {
            estado.textContent = 'No se pudieron guardar los gastos en este navegador.';
            estado.classList.add('error');
            return false;
        }
    }

    function actualizarFilas() {
        const filasActuales = [...filas.querySelectorAll('.fila-form-gasto')];
        filasActuales.forEach(fila => {
            fila.querySelector('.btn-quitar-fila-gasto').disabled = filasActuales.length === 1;
        });
    }

    function renderizarGastos() {
        const suma = gastos.reduce((acumulado, gasto) => acumulado + Number(gasto.amount), 0);
        total.textContent = formatoMoneda.format(suma);
        lista.replaceChildren();

        if (!gastos.length) {
            const vacio = document.createElement('li');
            vacio.className = 'lista-gastos-vacia';
            vacio.textContent = 'Todavía no hay gastos registrados.';
            lista.append(vacio);
            return;
        }

        gastos.forEach(gasto => {
            const elemento = document.createElement('li');
            const detalle = document.createElement('div');
            const nombre = document.createElement('strong');
            const fecha = document.createElement('span');
            const monto = document.createElement('strong');
            const botonQuitar = document.createElement('button');

            nombre.textContent = gasto.product;
            fecha.textContent = gasto.date || '';
            monto.textContent = `$ ${formatoMoneda.format(Number(gasto.amount))}`;
            botonQuitar.type = 'button';
            botonQuitar.className = 'btn-eliminar-gasto';
            botonQuitar.dataset.id = gasto.id;
            botonQuitar.setAttribute('aria-label', `Eliminar gasto de ${gasto.product}`);
            botonQuitar.title = 'Eliminar gasto';
            botonQuitar.textContent = '\u00d7';

            detalle.append(nombre, fecha);
            elemento.append(detalle, monto, botonQuitar);
            lista.append(elemento);
        });
    }

    function abrirModal() {
        modal.hidden = false;
        filas.querySelector('input[name="producto[]"]')?.focus();
    }

    function cerrarModal() {
        modal.hidden = true;
        botonAbrir.focus();
    }

    botonAbrir.addEventListener('click', abrirModal);
    botonCerrar?.addEventListener('click', cerrarModal);
    botonCancelar?.addEventListener('click', cerrarModal);

    modal.addEventListener('click', evento => {
        if (evento.target === modal) cerrarModal();
    });

    document.addEventListener('keydown', evento => {
        if (evento.key === 'Escape' && !modal.hidden) cerrarModal();
    });

    botonAgregar?.addEventListener('click', () => {
        filas.append(plantilla.content.cloneNode(true));
        actualizarFilas();
        filas.querySelector('.fila-form-gasto:last-child input[name="producto[]"]')?.focus();
    });

    filas.addEventListener('click', evento => {
        const botonQuitar = evento.target.closest('.btn-quitar-fila-gasto');
        if (!botonQuitar || filas.querySelectorAll('.fila-form-gasto').length === 1) return;

        botonQuitar.closest('.fila-form-gasto').remove();
        actualizarFilas();
    });

    lista.addEventListener('click', evento => {
        const botonQuitar = evento.target.closest('.btn-eliminar-gasto');
        if (!botonQuitar) return;

        const nuevosGastos = gastos.filter(gasto => String(gasto.id) !== botonQuitar.dataset.id);
        const gastosAnteriores = gastos;
        gastos = nuevosGastos;
        if (!guardarGastos()) {
            gastos = gastosAnteriores;
            return;
        }

        estado.textContent = '';
        estado.classList.remove('error');
        renderizarGastos();
    });

    window.addEventListener('caja:gastos-guardados', () => {
        gastos = [];
        guardarGastos();
        renderizarGastos();
    });

    formulario.addEventListener('submit', evento => {
        evento.preventDefault();
        const nuevasFilas = [...filas.querySelectorAll('.fila-form-gasto')];
        const registros = nuevasFilas.map(fila => ({
            product: fila.querySelector('input[name="producto[]"]').value.trim(),
            amount: Number(fila.querySelector('input[name="monto[]"]').value)
        }));

        if (registros.some(gasto => !gasto.product || !Number.isFinite(gasto.amount) || gasto.amount <= 0)) {
            estado.textContent = 'Completa el nombre y un monto mayor que cero en cada fila.';
            estado.classList.add('error');
            return;
        }

        const fecha = new Intl.DateTimeFormat('es-AR').format(new Date());
        const nuevosGastos = registros.map((gasto, indice) => ({
            id: `${Date.now()}-${indice}-${Math.random().toString(36).slice(2)}`,
            ...gasto,
            date: fecha
        }));
        const gastosAnteriores = gastos;
        gastos = [...nuevosGastos, ...gastos];

        if (!guardarGastos()) {
            gastos = gastosAnteriores;
            return;
        }

        renderizarGastos();
        estado.textContent = '';
        estado.classList.remove('error');
        formulario.reset();
        filas.querySelectorAll('.fila-form-gasto:not(:first-child)').forEach(fila => fila.remove());
        actualizarFilas();
        cerrarModal();
    });

    actualizarFilas();
    renderizarGastos();

}


// ============================================================
// CARGAR HISTORIAL
// ============================================================

function solicitarDatosServidor() {

    const etiquetaEstado =
        document.getElementById('estado-api');


    fetch(DIRECCION_API)

        .then(respuesta => {

            if (!respuesta.ok) {

                throw new Error(
                    `Error en el servidor. Código: ${respuesta.status}`
                );

            }

            return respuesta.json();

        })

        .then(listaDeRegistros => {

            construirFilasTabla(listaDeRegistros);

        })

        .catch(error => {

            console.error('Detalles del error:', error);


            if (etiquetaEstado) {

                etiquetaEstado.hidden = false;
                etiquetaEstado.textContent =
                    'Error al conectar con el servidor';
                etiquetaEstado.classList.add('estado-error');

            }

        });

}


// ============================================================
// CONSTRUIR TABLA
// ============================================================

function construirFilasTabla(registrosCaja) {

    const cuerpoTabla =
        document.querySelector('#tabla-datos tbody');


    if (!cuerpoTabla) {
        return;
    }


    cuerpoTabla.innerHTML = '';


    registrosCaja.forEach(registro => {

        const nuevaFila =
            document.createElement('tr');


        const fechaObjeto =
            registro.momento_cierre
                ? new Date(registro.momento_cierre)
                : new Date();


        const fechaFormateada =
            fechaObjeto.toLocaleDateString('es-AR', {

                day: '2-digit',

                month: '2-digit',

                year: 'numeric'

            });


        const horaFormateada =
            fechaObjeto.toLocaleTimeString('es-AR', {

                hour: '2-digit',

                minute: '2-digit'

            });


        const montoFormateado =
            parseFloat(
                registro.monto_cierre ?? registro.monto_total ?? 0
            ).toLocaleString('es-AR', {

                minimumFractionDigits: 2,

                maximumFractionDigits: 2

            });


        const datosFila = [
            fechaFormateada,
            horaFormateada,
            `$${montoFormateado}`,
            registro.usuario?.nombre || 'No disponible'
        ];

        datosFila.forEach((valor, indice) => {
            const celda = document.createElement('td');
            celda.textContent = valor;

            if (indice === 0 || indice === 2) {
                const destacado = document.createElement('strong');
                destacado.textContent = valor;
                celda.replaceChildren(destacado);
            }

            nuevaFila.appendChild(celda);
        });


        cuerpoTabla.appendChild(nuevaFila);

    });

}
