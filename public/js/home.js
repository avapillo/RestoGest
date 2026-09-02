// 1. Configuración de API y variables de estado global
const DIRECCION_API = '/api/historial-caja';
let cajaAbierta = false;
let acumuladoPagos = 0;

// 2. Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  solicitarDatosServidor();
  inicializarControlesCaja();
});

// 3. Función para asociar los eventos a los botones de la caja
function inicializarControlesCaja() {
  const btnAbrir = document.getElementById('btn-abrir-caja');
  const btnCerrar = document.getElementById('btn-cerrar-caja');
  const visorTotal = document.getElementById('total-pagos');

  if (!btnAbrir || !btnCerrar) {
    console.error('No se encontraron los botones de caja en el DOM.');
    return;
  }

  // Evento al presionar "Abrir Caja"
  btnAbrir.addEventListener('click', () => {
    cajaAbierta = true;
    acumuladoPagos = 0;

    if (visorTotal) {
      visorTotal.textContent = '0.00';
    }

    // Cambiar estados de los botones
    btnAbrir.disabled = true;
    btnCerrar.disabled = false;

    console.log('Caja abierta correctamente.');
  });

  // Evento al presionar "Cerrar Caja"
  btnCerrar.addEventListener('click', () => {
    if (!cajaAbierta) return;

    if (confirm(`¿Desea cerrar la caja con un total de $${acumuladoPagos.toFixed(2)}?`)) {
      cajaAbierta = false;

      // Habilitar / Deshabilitar botones
      btnAbrir.disabled = false;
      btnCerrar.disabled = true;

      // Opcional: Aquí llamas a tu API para guardar el cierre en la BD
      // enviarCierreServidor(acumuladoPagos);
    }
  });
}

// 4. Función para sumar pagos desde otras interfaces (Mesas / Para Llevar)
window.registrarNuevoPago = function(monto) {
  if (!cajaAbierta) {
    alert('Debe abrir la caja primero para poder registrar un pago.');
    return;
  }

  acumuladoPagos += parseFloat(monto);
  const visorTotal = document.getElementById('total-pagos');

  if (visorTotal) {
    visorTotal.textContent = acumuladoPagos.toLocaleString('es-CL', {
      minimumFractionDigits: 2
    });
  }
};

// 5. Carga de datos de la tabla desde la API
function solicitarDatosServidor() {
  const etiquetaEstado = document.getElementById('estado-api');

  fetch(DIRECCION_API)
    .then(respuestaBruta => {
      if (!respuestaBruta.ok) {
        throw new Error(`Error en el servidor. Código: ${respuestaBruta.status}`);
      }
      return respuestaBruta.json();
    })
    .then(listaDeRegistros => {
      construirFilasTabla(listaDeRegistros);
      if (etiquetaEstado) {
        etiquetaEstado.textContent = '✅ Datos sincronizados con éxito';
        etiquetaEstado.style.color = '#16a34a';
      }
    })
    .catch(error => {
      console.error('Detalles del error:', error);
      if (etiquetaEstado) {
        etiquetaEstado.textContent = '❌ Error al conectar con el servidor';
        etiquetaEstado.style.color = '#dc2626';
      }
    });
}

// 6. Inyección de datos en la tabla HTML
function construirFilasTabla(registrosCaja) {
  const cuerpoTabla = document.querySelector('#tabla-datos tbody');
  if (!cuerpoTabla) return;

  cuerpoTabla.innerHTML = '';

  registrosCaja.forEach(registro => {
    const nuevaFila = document.createElement('tr');
    const fechaObjeto = registro.momento_cierre ? new Date(registro.momento_cierre) : new Date();

    const fechaFormateada = fechaObjeto.toLocaleDateString('es-AR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });

    const horaFormateada = fechaObjeto.toLocaleTimeString('es-AR', {
      hour: '2-digit',
      minute: '2-digit'
    });

    const montoFormateado = parseFloat(registro.monto_total || 0).toLocaleString('es-CL');
    const nombreUsuario = registro.usuario_nombre || registro.usuario?.nombre || 'Sin usuario';

    nuevaFila.innerHTML = `
      <td><strong>${fechaFormateada}</strong></td>
      <td>${horaFormateada}</td>
      <td><strong>$${montoFormateado}</strong></td>
      <td>${nombreUsuario}</td>
    `;

    cuerpoTabla.appendChild(nuevaFila);
  });
}
