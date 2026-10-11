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

       $roles = RolUsuario::all();

        return view('interfaz_nuevo_usuario', compact('usuarios',"roles"));
    }

   public function nuevoUsuario(Request $request)
{
    $request->validate([
        'nombre'      => 'required|string|max:50',
        'gmail'       => 'required|email|max:255|unique:usuarios,gmail',
        'roles'       => 'required|array|min:1',
        'roles.*'     => 'exists:rol_usuario,id_rol',
    ], [
        'gmail.unique'  => 'No se pueden repetir los Gmail.',
        'roles.required' => 'Debes seleccionar un rol para el nuevo usuario.',
        'roles.min'      => 'Debes seleccionar un rol para el nuevo usuario.',
    ]);

    // 1. Crear el registro en la tabla 'usuarios'
    $usuario = Usuarios::create([
        'nombre' => $request->nombre,
        'gmail'  => $request->gmail,
    ]);

    // 2. Insertar los roles en la tabla pivote 'usuario_roles'
    if ($request->has('roles')) {
        $usuario->roles()->attach($request->roles);
    }

    return redirect()->route('usuario.index')->with('status', '¡Usuario registrado con éxito!');
}

    public function modificarUsuario(Request $request)
{
    $request->validate([
        'id'          => 'required|exists:usuarios,id',
        'nombre'      => 'required|string|max:50',
        'gmail'       => 'required|email|max:255|unique:usuarios,gmail,' . $request->id,
        'roles'       => 'nullable|array', // Recibe los nuevos roles seleccionados
        'roles.*'     => 'exists:rol_usuario,id_rol',
    ]);

    // 1. Actualizar datos del usuario
    $usuario = Usuarios::findOrFail($request->id);
    $usuario->nombre = $request->nombre;
    $usuario->gmail = $request->gmail;
    $usuario->save();

    // 2. Sincronizar roles en la tabla pivote 'usuario_roles'
    $usuario->roles()->sync($request->input('roles', []));

    return redirect()->route('usuario.index')->with('status', '¡Usuario modificado con éxito!');
}

    public function eliminarUsuario($id){

    $usuarios = usuarios::findOrFail($id);

    $usuarios->delete();

     return redirect()->route('usuario.index')->with('status', '¡Usuario eliminado con éxito!');
    }
}
