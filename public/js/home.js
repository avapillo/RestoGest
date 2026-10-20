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


// ============================================================
// INICIALIZACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    cargarEstadoCaja();

    solicitarDatosServidor();

    inicializarControlesCaja();

});


// ============================================================
// ELEMENTOS
// ============================================================

function obtenerElementosCaja() {

    return {
        btnAbrir: document.getElementById('btn-abrir-caja'),
        btnCerrar: document.getElementById('btn-cerrar-caja'),
        visorTotal: document.getElementById('total-pagos')
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

            actualizarInterfazCaja();

        })

        .catch(error => {

            console.error('Error al obtener estado de caja:', error);

        });

}


// ============================================================
// BOTONES
// ============================================================

function inicializarControlesCaja() {

    const { btnAbrir, btnCerrar } = obtenerElementosCaja();

    if (!btnAbrir || !btnCerrar) {

        console.error('No se encontraron los controles de caja.');

        return;

    }


    // ABRIR CAJA
    btnAbrir.addEventListener('click', abrirCaja);


    // CERRAR CAJA
    btnCerrar.addEventListener('click', cerrarCaja);

}


// ============================================================
// ABRIR CAJA
// ============================================================

function abrirCaja() {

    if (cajaAbierta) {
        return;
    }

    fetch(API_ABRIR, {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCSRF()
        }

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

        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCSRF()
        }

    })

    .then(respuesta => {

        if (!respuesta.ok) {
            throw new Error('No se pudo cerrar la caja');
        }

        return respuesta.json();

    })

    .then(datos => {

        cajaAbierta = false;
        acumuladoPagos = 0;

        actualizarInterfazCaja();

        solicitarDatosServidor();

        alert('Caja cerrada correctamente.');

    })

    .catch(error => {

        console.error(error);

        alert('No se pudo cerrar la caja.');

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
        visorTotal
    } = obtenerElementosCaja();


    if (visorTotal) {

        visorTotal.textContent = formatearMonto(acumuladoPagos);

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


            if (etiquetaEstado) {

                etiquetaEstado.textContent = '';
                etiquetaEstado.hidden = true;
                etiquetaEstado.classList.remove('estado-error');

            }

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
                registro.monto_total || 0
            ).toLocaleString('es-AR', {

                minimumFractionDigits: 2,

                maximumFractionDigits: 2

            });


        const nombreUsuario =
            registro.usuario_nombre ||
            registro.usuario?.nombre ||
            'Sin usuario';


        nuevaFila.innerHTML = `

            <td>
                <strong>${fechaFormateada}</strong>
            </td>

            <td>
                ${horaFormateada}
            </td>

            <td>
                <strong>$${montoFormateado}</strong>
            </td>

            <td>
                ${nombreUsuario}
            </td>

        `;


        cuerpoTabla.appendChild(nuevaFila);

    });

}
