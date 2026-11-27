<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
    <title>Clientes y pendientes - Sandwichería Gabriel</title>
    <link rel="stylesheet" href="{{ asset('css/ventas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pendientes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-adapter.css') }}">
</head>
<body>

<div class="contenedor-sitio administracion-layout">

    <!-- BARRA LATERAL -->
    <aside class="barra-lateral">
        <div class="logo-sistema">
            <img src="{{ asset('img/Sanwicheria Gabriel icon.png') }}" alt="Sandwichería Gabriel">
        </div>

        <nav class="menu-navegacion">
            <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
            <a href="{{ route('mesas.index') }}" class="opcion-menu">Mesas</a>
            <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
            <a href="{{ route('usuario.index') }}" class="opcion-menu">Control de Usuario</a>
            <a href="{{ route('ventas.index') }}" class="opcion-menu">Administración de Ventas</a>
            <a href="{{ route('pendientes.index') }}" class="opcion-menu activa">Pendientes</a>
        </nav>

        <div class="cerrar-sesion-contenedor">
            <a href="{{ route('logout') }}" class="cerrar-sesion">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal pendientes-contenido">

        <header class="encabezado-seccion">
            <div>
                <p class="sobre-titulo">Administración</p>
                <h1>Clientes y pendientes</h1>
                <p class="resumen-encabezado">
                Registrá movimientos y encontrá clientes por nombre o DNI.
                </p>
            </div>
        </header>

        <!-- MENSAJES -->
        @if (session('status'))
            <div class="pendientes-mensaje exito">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="pendientes-mensaje error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- ALTA DE MOVIMIENTO Y CLIENTE -->
        <section class="pendientes-panel">
            <form action="{{ route('pendientes.store') }}"
                  method="POST"
                  class="pendientes-formulario"
                  id="formNuevoRegistro"
                  data-search-url="{{ route('pendientes.buscar') }}">

                @csrf

                <input type="hidden" name="cliente_id" id="cliente_id">

                <div class="pendientes-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="38" height="38"
                         viewBox="0 0 24 24"
                         fill="currentColor"
                         aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0z"/>
                    </svg>
                </div>

                <div class="grupo-campo">
                    <label for="nombre">Nombre del cliente</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        maxlength="150"
                        title="El nombre solo puede contener letras y espacios."
                        required
                        autocomplete="name"
                        placeholder="Ingrese el nombre del cliente"
                        value="{{ old('nombre') }}">
                </div>

                <div class="grupo-campo">
                    <label for="monto">Cantidad ($)</label>
                    <input
                        type="number"
                        id="monto"
                        name="monto"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        required
                        placeholder="Ej: 18000"
                        value="{{ old('monto') }}">
                </div>

                <div class="grupo-campo" id="grupoDniCliente" hidden>
                    <label for="dni_ultimos_4">Últimos 4 dígitos del DNI</label>
                    <input
                        type="text"
                        id="dni_ultimos_4"
                        name="dni_ultimos_4"
                        inputmode="numeric"
                        autocomplete="off"
                        pattern="[0-9]{4}"
                        maxlength="4"
                        placeholder="Ej: 0042"
                        aria-describedby="ayudaDniCliente">
                    <small id="ayudaDniCliente">Se usa para distinguir a clientes con el mismo nombre.</small>
                </div>

                <p id="seleccionCliente" class="pendientes-seleccion-cliente" role="status" aria-live="polite" hidden></p>
                <p id="estadoAltaCliente" class="pendientes-estado-flujo" role="status" aria-live="polite" hidden></p>
                <div id="coincidenciasCliente" class="pendientes-coincidencias" hidden></div>

                <button type="submit" class="pendientes-btn-agregar" id="btnRegistrarCliente">
                    Registrar
                </button>
            </form>
        </section>

        <!-- BUSCADOR ÚNICO -->
        <section class="pendientes-panel">
            <h2 class="pendientes-panel-titulo">Buscar clientes y movimientos</h2>
            <form id="formBuscarClientes"
                  class="pendientes-buscador"
                  data-search-url="{{ route('pendientes.buscar') }}">
                <label for="busquedaCliente">Nombre o últimos 4 dígitos del DNI</label>
                <div class="pendientes-buscador-campo">
                    <input
                        type="search"
                        id="busquedaCliente"
                        maxlength="150"
                        placeholder="Escribí un nombre o exactamente 4 dígitos">
                    <button type="submit" class="pendientes-btn-agregar">Buscar</button>
                </div>
                <p class="pendientes-ayuda-busqueda">El nombre se busca parcialmente; el DNI, por coincidencia exacta.</p>
            </form>
            <div id="resultadosBusqueda" class="pendientes-resultados-busqueda" role="status" aria-live="polite"></div>
        </section>

        <!-- HISTORIAL DE MOVIMIENTOS -->
        <section class="pendientes-panel pendientes-panel-tabla">
            <h2 class="pendientes-panel-titulo pendientes-panel-titulo-tabla">Movimientos recientes</h2>

            <div class="pendientes-tabla-envoltura">
                <table class="pendientes-tabla">
                    <thead>
                        <tr>
                            <th>Fecha del registro</th>
                            <th>Cliente</th>
                            <th>DNI (últimos 4)</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pendientes as $pendiente)
                            <tr>
                                <td data-label="Fecha">
                                    {{ $pendiente->fecha_registro?->format('d/m/Y H:i') ?? '—' }}
                                </td>

                                <td data-label="Cliente">
                                    {{ $pendiente->cliente->nombre }}
                                </td>

                                <td data-label="DNI (últimos 4)">
                                    {{ $pendiente->cliente->dni_ultimos_4 ?? '—' }}
                                </td>

                                <td data-label="Monto" class="pendientes-monto">
                                    ${{ number_format((float) $pendiente->monto, 2, ',', '.') }}
                                </td>

                                <td data-label="Acciones">
                                    <div class="pendientes-acciones">

                                        <button
                                            type="button"
                                            class="pendientes-btn-editar btn-editar-pendiente"
                                            data-url="{{ route('pendientes.update', $pendiente->id) }}"
                                            data-nombre="{{ $pendiente->cliente->nombre }}"
                                            data-monto="{{ $pendiente->monto }}">
                                            ✏️ Editar
                                        </button>

                                        <form
                                            action="{{ route('pendientes.destroy', $pendiente->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Estás seguro de eliminar este pendiente?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="pendientes-btn-eliminar">
                                                🗑️ Eliminar
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="pendientes-vacio">
                                    Todavía no hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </section>

        <!-- AVISO INFORMATIVO -->
        <div class="pendientes-aviso">
            <span class="pendientes-aviso-icono">i</span>
            <span>
                Cada movimiento queda vinculado al identificador del cliente; los nombres repetidos no mezclan sus registros.
            </span>
        </div>

    </main>
</div>

<!-- MODAL PARA EDITAR -->
<div id="modalEditarPendiente" class="pendientes-modal" hidden>

    <div class="pendientes-modal-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloEditarPendiente">

        <h2 id="tituloEditarPendiente">Editar movimiento</h2>

        <form id="formEditarPendiente" method="POST">

            @csrf
            @method('PUT')

            <div class="grupo-campo">
                <label for="editar_nombre">Cliente</label>
                <input
                    type="text"
                    id="editar_nombre"
                    name="nombre"
                    maxlength="150"
                    title="El nombre solo puede contener letras y espacios."
                    readonly>
            </div>

            <div class="grupo-campo">
                <label for="editar_monto">Monto ($)</label>
                <input type="number" id="editar_monto" name="monto" min="0.01" step="0.01" required>
            </div>

            <div class="pendientes-modal-acciones">
                <button type="button" id="btnCancelarEdicion" class="pendientes-btn-cancelar">
                    Cancelar
                </button>

                <button type="submit" class="pendientes-btn-agregar">
                    Guardar cambios
                </button>
            </div>

        </form>
    </div>
</div>

 <script src="{{ asset('js/pendientes.js') }}"></script>

</body>
</html>
