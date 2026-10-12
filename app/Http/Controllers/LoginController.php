<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'gmail'       => 'required|email',
        ], [
            'nombre.required'      => 'El usuario es obligatorio.',
            'gmail.required'       => 'El correo electrónico es obligatorio.',
            'gmail.email'          => 'Ingresa un correo electrónico válido.',
        ]);

        // 2. Consulta a la BD (tabla usuario)
        $usuario = DB::table('usuarios')
            ->where('nombre', $request->nombre)
            ->where('gmail', $request->gmail)
            ->first();

        // 3. Si existe el usuario: Guardamos sesión y REDIRECCIONAMOS a la vista deseada
        if ($usuario) {
            session([
                'usuario_id'  => $usuario->id,
                'usuario_nom' => $usuario->nombre
            ]);

            // Redirige a la ruta nombrada 'producto.index' (o 'home')
            return redirect()->route('home');
        }

        // 4. Si las credenciales fallan: Volvemos atrás con mensaje de error
        return back()
            ->with('error', 'Usuario o correo electrónico incorrectos.')
            ->withInput($request->only('nombre'));
    }

    // Cerrar Sesión
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }


}
