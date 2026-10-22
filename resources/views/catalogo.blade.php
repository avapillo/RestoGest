<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Sandwichería Gabriel</title>

    <style>
        body{
            margin:0;
            background:#f4f4f4;
            font-family:Arial,Helvetica,sans-serif;
        }

        header{
            background:#174A75;
            color:white;
            padding:20px;
            text-align:center;
        }

        .contenedor{
            max-width:1200px;
            margin:auto;
            padding:20px;

            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 3px 10px rgba(0,0,0,.15);
        }

        .card img{
            width:100%;
            height:220px;
            object-fit:cover;
        }

        .sin-imagen{
            width:100%;
            height:220px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#ddd;
            font-size:40px;
        }

        .info{
            padding:15px;
        }

        .nombre{
            font-size:20px;
            font-weight:bold;
            color:#174A75;
        }

        .categoria{
            color:#666;
            margin-top:8px;
        }

        .precio{
            margin-top:10px;
            color:#0d7a2d;
            font-size:22px;
            font-weight:bold;
        }
    </style>
</head>

<body>

<header>
    <h1>Sandwichería Gabriel</h1>
    <h3>Catálogo de Productos</h3>
</header>

<div class="contenedor">

@foreach($productos as $producto)

<div class="card">

@if($producto->imagen)

<img src="{{ asset('storage/'.$producto->imagen) }}" alt="{{ $producto->nombre }}">

@else

<div class="sin-imagen">
🍔
</div>

@endif

<div class="info">

<div class="nombre">
{{ $producto->nombre }}
</div>

<div class="categoria">
{{ $producto->categoria->categoria ?? '' }}
</div>

<div class="precio">
$ {{ number_format($producto->precio,0,',','.') }}
</div>

</div>

</div>

@endforeach

</div>

</body>
</html>