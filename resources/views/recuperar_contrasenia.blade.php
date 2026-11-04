
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
    <title>Recuperar contraseña - RestoGest</title>
    <link rel="stylesheet" href="{{ asset('css/style_login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <div class="brand-logo"><i class="fa-solid fa-key"></i></div>
            <h2>Recuperar contraseña</h2>
            <p>Ingresá tu Gmail para recibir un enlace de recuperación</p>
        </div>

        @if (session('success'))
            <div class="alerta-exito">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alerta-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('contrasenia.enviar') }}" method="POST" class="login-form">
            @csrf

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
            </div>

            <button type="submit" class="btn-login">
                Enviar enlace <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>

        <div style="text-align:center; margin-top:20px;">
            <a href="{{ route('login') }}" style="color:inherit; text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión
            </a>
        </div>
    </div>

</body>
</html>
