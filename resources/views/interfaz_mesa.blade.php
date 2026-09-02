<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedidos de Mozos - Sandwichería Gabriel</title>
  <link rel="stylesheet" href="{{ asset('css/style_producto.css') }}">
</head>
<body>

  <div class="contenedor-sitio">

    <!-- BARRA LATERAL (MENU) -->
    <aside class="barra-lateral">
      <div class="logo-sistema">
        <h3>Sandwichería Gabriel</h3>
      </div>
      <nav class="menu-navegacion">
        <a href="{{ route('home') }}" class="opcion-menu">Principal</a>
        <a href="{{ route('intefaz_mesa') }}" class="opcion-menu activa">Mesas</a>
        <a href="{{ route('producto.index') }}" class="opcion-menu">Producto</a>
        <a href="{{ route('interfaz_paraLlevar') }}" class="opcion-menu">Para Llevar</a>
        <a href="#" class="opcion-menu">Ventas / Pedidos</a>
      </nav>
    </aside>




</body>
</html>
