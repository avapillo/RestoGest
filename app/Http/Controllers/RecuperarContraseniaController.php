<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class RecuperarContraseniaController extends Controller
{
    // Mostrar formulario para solicitar recuperación
    public function mostrarFormulario()
    {
        return view('recuperar_contrasenia');
    }

    // Procesar solicitud y enviar enlace
    public function enviarEnlace(Request $request)
    {
        $request->validate([
            'gmail' => ['required', 'email', 'ends_with:@gmail.com'],
        ]);

        $gmail = strtolower(trim($request->gmail));

        $usuario = DB::table('usuarios')
            ->where('gmail', $gmail)
            ->first();

        // No revelar si el correo existe
        $mensaje = 'Si el correo está registrado, recibirás un enlace de recuperación.';

        if (!$usuario) {
            return back()->with('success', $mensaje);
        }

        // Crear token aleatorio seguro
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $gmail],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // Enlace firmado con vencimiento de 30 minutos
        $url = URL::temporarySignedRoute(
            'contrasenia.formulario',
            now()->addMinutes(30),
            [
                'gmail' => $gmail,
                'token' => $token,
            ]
        );

        try {
            Mail::raw(
                "Hola {$usuario->nombre},\n\nPara recuperar tu contraseña ingresá al siguiente enlace:\n\n{$url}\n\nEste enlace vence en 30 minutos.",
                function ($message) use ($gmail) {
                    $message->to($gmail)
                        ->subject('Recuperación de contraseña - RestoGest');
                }
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'gmail' => 'No se pudo procesar el envío. Intentá nuevamente más tarde.',
            ]);
        }

        return back()->with('success', $mensaje);
    }

    // Mostrar formulario para establecer contraseña nueva
    public function mostrarNuevaContrasenia(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'El enlace de recuperación no es válido o ha vencido.');
        }

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->query('gmail'))
            ->first();

        if (
            !$registro ||
            !Hash::check($request->query('token', ''), $registro->token) ||
            Carbon::parse($registro->created_at)->addMinutes(30)->isPast()
        ) {
            abort(403, 'El enlace de recuperación no es válido o ha vencido.');
        }

        return view('nueva_contrasenia', [
            'gmail' => $request->query('gmail'),
            'token' => $request->query('token'),
        ]);
    }

    // Guardar contraseña nueva
    public function actualizarContrasenia(Request $request)
    {
        $request->validate([
            'gmail' => ['required', 'email'],
            'token' => ['required', 'string'],
            'contrasenia' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->gmail)
            ->first();

        if (
            !$registro ||
            !Hash::check($request->token, $registro->token) ||
            Carbon::parse($registro->created_at)->addMinutes(30)->isPast()
        ) {
            return back()->withErrors([
                'contrasenia' => 'El enlace no es válido o ha vencido.',
            ]);
        }

        $actualizado = DB::table('usuarios')
            ->where('gmail', $request->gmail)
            ->update([
                'contrasenia' => Hash::make($request->contrasenia),
            ]);

        if (!$actualizado) {
            return back()->withErrors([
                'contrasenia' => 'No se pudo actualizar la contraseña.',
            ]);
        }

        DB::table('password_reset_tokens')
            ->where('email', $request->gmail)
            ->delete();

        return redirect()->route('login')
            ->with('success', 'Tu contraseña fue actualizada correctamente. Ya podés iniciar sesión.');
    }
}
