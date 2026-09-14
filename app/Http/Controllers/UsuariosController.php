<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\RolUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UsuariosController extends Controller
{
      public function mostrarUsuarios(Request $request){
         $usuarios = usuarios::with('roles')->get();

         // Consulta la tabla 'rol_usuario' directamente SIN usar su modelo
         $roles = DB::table('rol_usuario')->get();
        // $roles = RolUsuario::all();

        return view('interfaz_nuevo_usuario', compact('usuarios',"roles"));
    }

    public function nuevoUsuario(Request $request){
        $request->validate([
           'nombre'         => 'required|string|max:255',
           'contrasenia'    => 'required|string|max:255',
            'id_rol_usuario' => 'required|exists:rol_usuario,id_rol',
        ]);

        Usuarios::create([
            'nombre'         => $request->nombre,
            'contrasenia'    => $request->contrasenia,
            'id_rol_usuario' => $request->id_rol_usuario,
        ]);

        return redirect()->route('usuario.index')->with('status', '¡Usuario registrado con éxito!');
    }

    public function modificarUsuario(Request $request){
        $request->validate([
            'id'             => 'required|exists:usuarios,id',
            'nombre'         => 'required|string|max:255',
            'contrasenia'    => 'required|string|max:255',
            'id_rol_usuario' => 'required|exists:rol_usuario,id_rol',
        ]);

        $usuario = Usuarios::findOrFail($request->id);
        $usuario->nombre = $request->nombre;
        $usuario->contrasenia = $request->contrasenia;
        $usuario->id_rol_usuario = $request->id_rol_usuario;
        $usuario->save();

        return redirect()->route('usuario.index')->with('status', '¡Usuario modificado con éxito!');
    }

    public function eliminarUsuario($id){

    $usuarios = usuarios::findOrFail($id);

    $usuarios->delete();

     return redirect()->route('usuario.index')->with('status', '¡Usuario eliminado con éxito!');
    }
}
