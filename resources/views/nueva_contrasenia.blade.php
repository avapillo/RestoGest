<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/Sanwicheria Gabriel icon.png') }}">
    <title>Nueva contraseña - RestoGest</title>
    <link rel="stylesheet" href="{{ asset('css/style_login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <div class="brand-logo"><i class="fa-solid fa-lock"></i></div>
            <h2>Nueva contraseña</h2>
            <p>Ingresá y confirmá tu nueva contraseña</p>
        </div>

        @if ($errors->any())
            <div class="alerta-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('contrasenia.actualizar') }}" method="POST" class="login-form">
            @csrf

            <input type="hidden" name="gmail" value="{{ $gmail }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="contrasenia">Nueva contraseña</label>
                <div class="input-container">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="contrasenia"
                        name="contrasenia"
                        placeholder="Mínimo 8 caracteres"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="contrasenia_confirmation">Confirmar contraseña</label>
                <div class="input-container">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="contrasenia_confirmation"
                        name="contrasenia_confirmation"
                        placeholder="Repetí tu contraseña"
                        minlength="8"
                        autocomplete="new-password"
                        required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Guardar nueva contraseña
                <i class="fa-solid fa-check"></i>
            </button>
        </form>
    </div>

</body>
</html>
