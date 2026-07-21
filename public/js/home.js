document.addEventListener('DOMContentLoaded', () => {

    // 1. NAVEGACIÓN ENTRE PÁGINAS (BOTONES DE LA BARRA LATERAL)
    const navButtons = document.querySelectorAll('.nav-btn');

    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetUrl = this.getAttribute('data-url');

            // Marcar botón activo dinámicamente
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            if (targetUrl) {
                // Redirección a la ruta de Laravel
                window.location.href = targetUrl;
            }
        });
    });

    // 2. ACCIONES DE CAJA (ABRIR / CERRAR CAJA)
    const btnAbrirCaja = document.getElementById('btn-abrir-caja');
    const btnCerrarCaja = document.getElementById('btn-cerrar-caja');

    btnAbrirCaja.addEventListener('click', () => {
        // Ejemplo de petición fetch a backend de Laravel para abrir caja
        fetch('/api/caja/abrir', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Caja abierta con éxito');
        })
        .catch(err => alert('Abrir caja activado (Modo local)'));
    });

    btnCerrarCaja.addEventListener('click', () => {
        if(confirm('¿Está seguro de realizar el cierre de caja?')) {
            fetch('/api/caja/cerrar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Caja cerrada correctamente');
            })
            .catch(err => alert('Cerrar caja activado (Modo local)'));
        }
    });

    // 3. CONSULTAR Y ACTUALIZAR DATOS EN TIEMPO REAL DESDE LA BASE DE DATOS
    function cargarDatosDashboard() {
        fetch('/api/dashboard/metricas')
            .then(response => response.json())
            .then(data => {
                // Actualizar total ventas
                if(data.ventasDia !== undefined) {
                    document.getElementById('ventas-dia').textContent = `$${formatNumber(data.ventasDia)}`;
                }

                // Actualizar Tabla de Ventas
                if(data.ventas) {
                    actualizarTablaVentas(data.ventas);
                }

                // Actualizar Lista Mas Vendidos
                if(data.masVendidos) {
                    actualizarMasVendidos(data.masVendidos);
                }
            })
            .catch(error => console.log('Servicio API no conectado aun, usando datos estáticos del layout.'));
    }

    // Funciones Auxiliares
    function actualizarTablaVentas(ventas) {
        const tbody = document.getElementById('tabla-ventas-body');
        tbody.innerHTML = '';

        ventas.forEach(venta => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${venta.fecha}</td>
                <td>${venta.hora}</td>
                <td>$${formatNumber(venta.total)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function actualizarMasVendidos(items) {
        const list = document.getElementById('lista-mas-vendidos');
        list.innerHTML = '';

        items.forEach(item => {
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="product-name">${item.nombre}</span>
                <span class="product-qty">${item.cantidad}</span>
            `;
            list.appendChild(li);
        });
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('es-AR').format(num);
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // Opcional: Actualizar datos automáticamente cada 30 segundos
    // setInterval(cargarDatosDashboard, 30000);
});
