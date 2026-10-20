<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
    <title>Iniciar Sesión - RestoGest</title>
    <link rel="stylesheet" href="{{ asset('css/style_login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <div class="brand-logo"><i class="fa-solid fa-utensils"></i></div>
            <h2>RestoGest</h2>
            <p>Ingresa tus credenciales para acceder</p>
        </div>

        <!-- 🔴 Mensaje de error general si fallan las credenciales -->
        @if (session('error'))
            <div class="alerta-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario puro HTML -> apunta directamente a la ruta PHP -->
        <form action="{{ route('login.post') }}" method="POST" class="login-form">
            @csrf <!-- Token de seguridad obligatorio en Laravel -->

            <!-- Campo Nombre de Usuario -->
            <div class="form-group">
                <label for="nombre">Nombre de Usuario</label>
                <div class="input-container">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input
                        type="text"
                        id="id"
                        name="nombre"
                        placeholder="Ej: Gabriel"
                        value="{{ old('nombre') }}"
                        required
                        autofocus>
                </div>
                @error('nombre')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campo Gmail -->
            <div class="form-group">
                <label for="gmail">Gmail</label>
                <div class="input-container">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="gmail"
                        name="gmail"
                        placeholder="Ej: nombre@gmail.com"
                        value="{{ old('gmail') }}"
                        required>
                </div>
                @error('gmail')
                    <span class="error-texto">{{ $message }}</span>
                @enderror
            </div>

            <!-- Botón de Envío -->
            <button type="submit" class="btn-login">
                Iniciar Sesión <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

</body>
</html>
