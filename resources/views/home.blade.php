<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanwicheria Gabriel</title>

    <!-- CSS del proyecto -->
    <link rel="stylesheet" href="{{ asset('css/home_styles.css') }}">

    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="app-container">
        <!-- BARRA LATERAL / NAVEGACIÓN -->
        <aside class="sidebar">
            <div class="user-avatar-placeholder">
                <i class="fa-solid fa-user"></i>
            </div>


             <!-- NAVEGACIÓN DE LA BARRA LATERAL -->
            <nav class="sidebar-nav">
                <button class="nav-btn active" data-url="{{ route('home') }}" title="Inicio">
                    <i class="fa-solid fa-house"></i>
                </button>
                <button class="nav-btn" data-url="{{ route('comandas.index') }}" title="Comandas / Pedidos">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="nav-btn" data-url="{{ route('mesas.index') }}" title="Mesas">
                    <i class="fa-solid fa-utensils"></i>
                </button>
                <button class="nav-btn" data-url="{{ route('cocina.index') }}" title="Cocina">
                    <i class="fa-solid fa-bucket"></i>
                </button>
                <button class="nav-btn" data-url="{{ route('reportes.index') }}" title="Estadísticas / Reportes">
                    <i class="fa-solid fa-chart-pie"></i>
                </button>
                <button class="nav-btn" data-url="{{ route('historial.index') }}" title="Historial / Turnos">
                    <i class="fa-solid fa-business-time"></i>
                </button>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <!-- HEADER -->
            <header class="main-header">
                <h1 class="brand-title">Sanwicheria Gabriel</h1>
                <div class="date-badge" id="current-date">
                    {{ $fechaActual ?? '20/05/26' }}
                </div>
            </header>

            <!-- BARRA DE ACCIONES Y MÉTRICAS -->
            <section class="actions-bar">
                <button class="btn btn-action" id="btn-abrir-caja">Abrir Caja</button>

                <div class="metric-card">
                    <span class="metric-label">Ventas del Día</span>
                    <div class="metric-value" id="ventas-dia">
                        ${{ number_format($totalVentasDia ?? 456000, 0, ',', '.') }}
                    </div>
                </div>

                <button class="btn btn-action" id="btn-cerrar-caja">Cerrar Caja</button>
            </section>



            <!-- PANEL INFERIOR CON TABLA Y LO MÁS VENDIDO / HISTORIAL DE VENTAS-->
            <section class="dashboard-grid">
                <!-- TABLA DE VENTAS -->
                <div class="table-card">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-ventas-body">
                            {{-- Renderizado Blade inicial desde la BD --}}
                            @forelse($ventas ?? [] as $venta)
                                <tr>
                                    <td>{{ $venta->fecha }}</td>
                                    <td>{{ $venta->hora }}</td>
                                    <td>${{ number_format($venta->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                {{-- Fila de muestra idéntica al diseño si no hay datos pasados --}}
                                <tr>
                                    <td>04/05/26</td>
                                    <td>12:34 AM</td>
                                    <td>$258.000</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PANEL LO MÁS VENDIDO HOY -->
                <div class="top-products-card">
                    <h2 class="card-title">Los más Vendido Hoy</h2>
                    <div class="top-products-header">
                        <span>Platos:</span>
                        <span>Cantidad:</span>
                    </div>
                    <ul class="top-products-list" id="lista-mas-vendidos">
                        @forelse($masVendidos ?? [] as $item)
                            <li>
                                <span class="product-name">{{ $item->nombre }}</span>
                                <span class="product-qty">{{ $item->cantidad }}</span>
                            </li>
                        @empty
                            {{-- Datos de muestra según captura --}}
                            <li>
                                <span class="product-name">Lomito con papas</span>
                                <span class="product-qty">8</span>
                            </li>
                            <li>
                                <span class="product-name">Lomito</span>
                                <span class="product-qty">4</span>
                            </li>
                            <li>
                                <span class="product-name">Pizza comun</span>
                                <span class="product-qty">1</span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <!-- JS del proyecto -->
    <script src="{{ asset('js/home.js') }}"></script>
</body>
</html>
