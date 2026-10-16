<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Login;

class LoginController extends Controller
{
    // Muestra la vista del formulario  Milton 2580
    public function mostrarLogin()
    {
        return view('interfaz_login');
    }

    // Valida y procesa el formulario enviado
    public function validarUsuario(Request $request)
    {
        // 1. Validaciones
        $request->validate([
            'nombre'      => 'required|string',
            'gmail'       => 'nullable|required_without:contrasenia|email',
            'contrasenia' => 'nullable|required_without:gmail',
        ], [
            'nombre.required'      => 'El usuario es obligatorio.',
            'gmail.required_without' => 'Ingresa el correo electrónico o la contraseña.',
            'gmail.email'          => 'Ingresa un correo electrónico válido.',
        ]);

        // 2. Consulta al usuario y valida el dato disponible en el esquema.
        $usuario = DB::table('usuarios')
            ->where('nombre', $request->nombre)
            ->first();

        $credencialesValidas = false;

        if ($usuario && $request->filled('gmail') && Schema::hasColumn('usuarios', 'gmail')) {
            $credencialesValidas = hash_equals((string) $usuario->gmail, (string) $request->gmail);
        } elseif ($usuario && $request->filled('contrasenia') && Schema::hasColumn('usuarios', 'contrasenia')) {
            $credencialesValidas = hash_equals((string) $usuario->contrasenia, (string) $request->contrasenia);
        }

        // 3. Si existe el usuario: Guardamos sesión y REDIRECCIONAMOS a la vista deseada
        if ($credencialesValidas) {
            session([
                'usuario_id'  => $usuario->id,
                'usuario_nom' => $usuario->nombre
            ]);

            // Redirige a la ruta nombrada 'producto.index' (o 'home')
            return redirect()->route('home');
        }

        // 4. Si las credenciales fallan: Volvemos atrás con mensaje de error
        return back()
            ->with('error', 'Usuario o contraseña incorrectos.')
            ->withInput($request->only('nombre'));
    }

    // Cerrar Sesión
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }


}
