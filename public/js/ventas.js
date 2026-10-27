document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#filtrosVentas');
  const tbody = document.querySelector('#filasVentas');
  const status = document.querySelector('#estadoVentas');
  const summary = document.querySelector('#resumenResultados');
  const pageLabel = document.querySelector('#paginaActual');
  const previousButton = document.querySelector('#paginaAnterior');
  const nextButton = document.querySelector('#paginaSiguiente');
  const searchButton = form.querySelector('button[type="submit"]');
  const searchUrl = form.dataset.apiUrl;
  const currencyFormatter = new Intl.NumberFormat('es-CL', {
    maximumFractionDigits: 2,
  });

  let currentPage = 1;
  let lastPage = 1;
  let activeRequest;

  function setStatus(message, isError = false) {
    status.textContent = message;
    status.classList.toggle('error', isError);
  }

  function addCell(row, value, className = '', label = '') {
    const cell = document.createElement('td');
    cell.textContent = value ?? '-';
    if (className) cell.className = className;
    if (label) cell.dataset.label = label;
    row.append(cell);
  }

  function formatPaymentDate(value) {
    if (!value) return '-';

    const [date, time] = String(value).split(/[T ]/);
    const [year, month, day] = date.split('-').map(Number);
    const formattedDate = new Intl.DateTimeFormat('es-CL').format(new Date(year, month - 1, day));

    return time ? `${formattedDate} ${time.slice(0, 5)}` : formattedDate;
  }

  function renderRows(rows) {
    tbody.replaceChildren();

    if (!rows.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.className = 'fila-vacia';
      cell.colSpan = 4;
      cell.textContent = 'No se encontraron ventas con esos parámetros.';
      row.append(cell);
      tbody.append(row);
      return;
    }

    rows.forEach(venta => {
      const row = document.createElement('tr');
      row.className = 'fila-venta';

      // 1. Fecha en que se cobró la venta
      addCell(row, formatPaymentDate(venta.fecha_pago), '', 'Fecha de pago');

      // 2. Monto total
      addCell(
        row,
        venta.monto === null ? '-' : `$ ${currencyFormatter.format(Number(venta.monto))}`,
        'celda-monto',
        'Monto',
      );

      // 3. Método de pago
      addCell(row, venta.metodo_pago ?? '-', '', 'Tipo de pago');

      tbody.append(row);
    });
  }

  async function loadSales(page = 1) {
    activeRequest?.abort();
    const request = new AbortController();
    activeRequest = request;

    const parameters = new URLSearchParams();
    for (const [key, value] of new FormData(form).entries()) {
      if (value) parameters.set(key, value);
    }
    parameters.set('page', String(page));

    searchButton.disabled = true;
    previousButton.disabled = true;
    nextButton.disabled = true;
    setStatus('Buscando ventas...');

    try {
      const response = await fetch(`${searchUrl}?${parameters.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal: request.signal,
      });
      const result = await response.json();

      if (!response.ok) {
        const validationMessage = Object.values(result.errors ?? {}).flat()[0];
        throw new Error(validationMessage ?? result.message ?? 'No fue posible consultar las ventas.');
      }

      currentPage = result.current_page;
      lastPage = result.last_page;
      renderRows(result.data);
      pageLabel.textContent = `Página ${currentPage} de ${lastPage}`;
      summary.textContent = `${result.total} ${result.total === 1 ? 'venta encontrada' : 'ventas encontradas'}`;
      setStatus(result.total ? 'Resultados actualizados.' : 'No hay ventas para mostrar.');
      previousButton.disabled = currentPage <= 1;
      nextButton.disabled = currentPage >= lastPage;
    } catch (error) {
      if (error.name === 'AbortError') return;

      renderRows([]);
      summary.textContent = 'No se pudieron cargar los resultados';
      pageLabel.textContent = 'Página 1 de 1';
      setStatus(error.message, true);
    } finally {
      if (!request.signal.aborted) searchButton.disabled = false;
    }
  }

  form.addEventListener('submit', event => {
    event.preventDefault();
    loadSales(1);
  });

  form.addEventListener('reset', () => {
    window.setTimeout(() => loadSales(1), 0);
  });

  previousButton.addEventListener('click', () => {
    if (currentPage > 1) loadSales(currentPage - 1);
  });

  nextButton.addEventListener('click', () => {
    if (currentPage < lastPage) loadSales(currentPage + 1);
  });

  loadSales();
});
