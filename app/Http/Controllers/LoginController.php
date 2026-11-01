<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Login;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    // Muestra la vista del formulario  Milton 2580
    public function mostrarLogin()
    {
        return view('interfaz_login');
    }

    // Valida y procesa el formulario enviado
    public function validarUsuario(Request $request, AdminAccess $adminAccess)
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
            if (!$adminAccess->isAdmin((int) $usuario->id)) {
                return back()
                    ->with('error', 'Solo los usuarios con rol Admin pueden ingresar al sistema.')
                    ->withInput($request->only('gmail'));
            }

            if (!$isHashed) {
                DB::table('usuarios')->where('id', $usuario->id)->update([
                    'contrasenia' => Hash::make($request->contrasenia),
                ]);
            }

            $request->session()->regenerate();

            session([
                'usuario_id'  => $usuario->id,
                'usuario_nom' => $usuario->nombre
            ]);

            $adminAccess->record((int) $usuario->id, 'ingreso_sistema');

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


    // Inicio de sesión para la aplicación Flutter
    public function loginApp(Request $request)
    {
        $datos = $request->validate([
            'gmail' => 'required|email',
            'contrasenia' => 'required|string',
        ]);

        $gmail = strtolower(trim($datos['gmail']));

        $usuario = \App\Models\Usuarios::with('roles')
            ->where('gmail', $gmail)
            ->first();

        if (!$usuario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Correo o contraseña incorrectos.',
            ], 401);
        }

        $storedPassword = (string) $usuario->contrasenia;

        $isHashed = str_starts_with($storedPassword, '$2y$')
            || str_starts_with($storedPassword, '$argon2');

        $credentialsValid = $isHashed
            ? Hash::check($datos['contrasenia'], $storedPassword)
            : hash_equals($storedPassword, $datos['contrasenia']);

        if (!$credentialsValid) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Correo o contraseña incorrectos.',
            ], 401);
        }

        if (!$isHashed) {
            DB::table('usuarios')
                ->where('id', $usuario->id)
                ->update([
                    'contrasenia' => Hash::make($datos['contrasenia']),
                ]);
        }

        $roles = $usuario->roles->pluck('rol_nombre')
            ->map(fn($rol) => strtolower(trim($rol)));

        if (Schema::hasColumn('usuarios', 'id_rol_usuario')) {
            $rolDirecto = DB::table('usuarios')
                ->join('rol_usuario', 'usuarios.id_rol_usuario', '=', 'rol_usuario.id_rol')
                ->where('usuarios.id', $usuario->id)
                ->value('rol_usuario.rol_nombre');

            if ($rolDirecto) {
                $roles->push(strtolower(trim($rolDirecto)));
            }
        }

        $tienePermiso = $roles->contains(
            fn($rol) => in_array(
                $rol,
                ['admin', 'administrador', 'mozo'],
                true
            )
        );

        if (!$tienePermiso) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No tenés permiso para ingresar a esta aplicación.',
            ], 403);
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Inicio de sesión correcto.',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'gmail' => $usuario->gmail,
                'roles' => $roles->unique()->values(),
            ],
        ]);
    }

}
