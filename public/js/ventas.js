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
  const detailDialog = document.querySelector('#dialogoDetalleVenta');
  const detailLocation = document.querySelector('#ubicacionDetalleVenta');
  const detailStatus = document.querySelector('#estadoDetalleVenta');
  const detailRows = document.querySelector('#filasDetalleVenta');
  const closeDetailButton = document.querySelector('#cerrarDetalleVenta');
  const currencyFormatter = new Intl.NumberFormat('es-CL', {
    maximumFractionDigits: 2,
  });

  let currentPage = 1;
  let lastPage = 1;
  let activeRequest;
  let detailRequest;

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
      const amountCell = row.lastElementChild;
      amountCell.dataset.ventaId = venta.id_registro;
      amountCell.title = 'Haz doble clic para ver la mesa y los productos';
      amountCell.setAttribute('role', 'button');
      amountCell.tabIndex = 0;
      amountCell.setAttribute('aria-label', `Ver detalle de la venta por ${amountCell.textContent}`);

      // 3. Método de pago
      addCell(row, venta.metodo_pago ?? '-', '', 'Tipo de pago');

      tbody.append(row);
    });
  }

  function renderSaleDetails(details) {
    detailLocation.textContent = details.para_llevar
      ? 'Pedido para llevar'
      : `Sección: ${details.seccion ?? 'Sin sección'} · Mesa: ${details.numero_mesa ?? 'Sin mesa'}`;
    detailRows.replaceChildren();

    if (!details.productos.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.className = 'fila-vacia';
      cell.colSpan = 3;
      cell.textContent = 'Este pago no tiene productos asociados.';
      row.append(cell);
      detailRows.append(row);
      return;
    }

    details.productos.forEach(product => {
      const row = document.createElement('tr');
      row.className = 'fila-venta';
      addCell(row, product.nombre ?? 'Producto', '', 'Producto');
      addCell(row, product.cantidad ?? 0, '', 'Cantidad');
      addCell(
        row,
        product.precio === null ? '-' : `$ ${currencyFormatter.format(Number(product.precio))}`,
        'celda-monto',
        'Precio',
      );
      detailRows.append(row);
    });
  }

  async function loadSaleDetails(paymentId) {
    detailRequest?.abort();
    const request = new AbortController();
    detailRequest = request;

    if (!detailDialog.open) detailDialog.showModal();
    detailLocation.textContent = '';
    detailRows.replaceChildren();
    detailStatus.textContent = 'Cargando detalle...';
    detailStatus.classList.remove('error');

    try {
      const response = await fetch(
        `${searchUrl.replace(/\/$/, '')}/${encodeURIComponent(paymentId)}/detalle`,
        {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
          signal: request.signal,
        },
      );
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message ?? 'No fue posible consultar el detalle de la venta.');
      }

      renderSaleDetails(result);
      detailStatus.textContent = '';
    } catch (error) {
      if (error.name === 'AbortError') return;

      detailLocation.textContent = '';
      detailRows.replaceChildren();
      detailStatus.textContent = error.message;
      detailStatus.classList.add('error');
    }
  }

  tbody.addEventListener('dblclick', event => {
    const amountCell = event.target.closest('[data-venta-id]');
    if (amountCell) loadSaleDetails(amountCell.dataset.ventaId);
  });

  tbody.addEventListener('keydown', event => {
    const amountCell = event.target.closest('[data-venta-id]');
    if (amountCell && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      loadSaleDetails(amountCell.dataset.ventaId);
    }
  });

  closeDetailButton.addEventListener('click', () => detailDialog.close());
  detailDialog.addEventListener('close', () => detailRequest?.abort());
  detailDialog.addEventListener('click', event => {
    if (event.target === detailDialog) detailDialog.close();
  });

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

  const productForm = document.querySelector('#filtrosProductos');
  const productRows = document.querySelector('#filasProductos');
  const productStatus = document.querySelector('#estadoProductos');
  const productSummary = document.querySelector('#resumenProductos');
  const productSearchButton = productForm.querySelector('button[type="submit"]');
  const categorySelect = productForm.querySelector('#categoria_producto');
  const productSelect = productForm.querySelector('#producto_categoria');
  const availableProducts = Array.from(productSelect.options)
    .filter(option => option.dataset.categoriaId)
    .map(option => ({
      id: option.value,
      categoryId: option.dataset.categoriaId,
      name: option.textContent.trim(),
    }));
  let productRequest;

  function updateProductOptions() {
    const categoryId = categorySelect.value;
    productSelect.replaceChildren();

    if (!categoryId) {
      productSelect.add(new Option('Selecciona primero una categoría', ''));
      productSelect.disabled = true;
      return;
    }

    productSelect.add(new Option('Todos los productos de la categoría', ''));
    availableProducts
      .filter(product => product.categoryId === categoryId)
      .forEach(product => productSelect.add(new Option(product.name, product.id)));
    productSelect.disabled = false;
  }

  function renderProducts(products) {
    productRows.replaceChildren();

    if (!products.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.className = 'fila-vacia';
      cell.colSpan = 2;
      cell.textContent = 'No se encontraron productos vendidos con esos parámetros.';
      row.append(cell);
      productRows.append(row);
      return;
    }

    products.forEach(product => {
      const row = document.createElement('tr');
      row.className = 'fila-venta';
      addCell(row, product.nombre ?? 'Producto sin nombre', '', 'Producto');
      addCell(row, product.unidades_vendidas ?? 0, 'celda-monto', 'Unidades vendidas');
      productRows.append(row);
    });
  }

  async function loadProducts() {
    productRequest?.abort();
    const request = new AbortController();
    productRequest = request;
    const parameters = new URLSearchParams();

    for (const [key, value] of new FormData(productForm).entries()) {
      if (value) parameters.set(key, value);
    }

    productSearchButton.disabled = true;
    productStatus.textContent = 'Buscando productos...';
    productStatus.classList.remove('error');

    try {
      const response = await fetch(`${productForm.dataset.apiUrl}?${parameters.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal: request.signal,
      });
      const result = await response.json();

      if (!response.ok) {
        const validationMessage = Object.values(result.errors ?? {}).flat()[0];
        throw new Error(validationMessage ?? result.message ?? 'No fue posible consultar los productos.');
      }

      renderProducts(result);
      productSummary.textContent = `Top ${result.length} ${result.length === 1 ? 'producto' : 'productos'}`;
      productStatus.textContent = result.length ? 'Resultados actualizados.' : 'No hay productos vendidos para mostrar.';
    } catch (error) {
      if (error.name === 'AbortError') return;

      renderProducts([]);
      productSummary.textContent = 'No se pudieron cargar los productos';
      productStatus.textContent = error.message;
      productStatus.classList.add('error');
    } finally {
      if (!request.signal.aborted) productSearchButton.disabled = false;
    }
  }

  productForm.addEventListener('submit', event => {
    event.preventDefault();
    loadProducts();
  });

  productForm.addEventListener('reset', () => {
    window.setTimeout(() => {
      updateProductOptions();
      loadProducts();
    }, 0);
  });

  categorySelect.addEventListener('change', updateProductOptions);
  updateProductOptions();
  loadProducts();
});
