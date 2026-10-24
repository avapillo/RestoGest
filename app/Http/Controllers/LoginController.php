<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $request->merge(['gmail' => strtolower(trim((string) $request->input('gmail')))]);

        $request->validate([
            'gmail'       => 'required|email|ends_with:@gmail.com',
            'contrasenia' => 'required|string',
        ], [
            'gmail.email' => 'Ingresa un correo electrónico válido.',
            'gmail.ends_with' => 'Debes ingresar una dirección @gmail.com.',
        ]);

        $usuario = DB::table('usuarios')
            ->where('gmail', $request->gmail)
            ->first();

        $storedPassword = (string) ($usuario->contrasenia ?? '');
        $isHashed = str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$argon2');
        $credentialsValid = $usuario && ($isHashed
            ? Hash::check($request->contrasenia, $storedPassword)
            : hash_equals($storedPassword, (string) $request->contrasenia));

        if ($credentialsValid) {
            if (!$isHashed) {
                DB::table('usuarios')->where('id', $usuario->id)->update([
                    'contrasenia' => Hash::make($request->contrasenia),
                ]);
            }

            session([
                'usuario_id'  => $usuario->id,
                'usuario_nom' => $usuario->nombre
            ]);

            return redirect()->route('home');
        }

        return back()
            ->with('error', 'Usuario o contraseña incorrectos.')
            ->withInput($request->only('gmail'));
    }

    // Cerrar Sesión
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }


}
