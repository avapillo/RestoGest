<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\RolUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuariosController extends Controller
{
      public function mostrarUsuarios(Request $request){
         $usuarios = usuarios::with('roles')->get();

       $roles = RolUsuario::all();

        return view('interfaz_nuevo_usuario', compact('usuarios',"roles"));
    }

    public function mostrarRoles()
    {
        $roles = RolUsuario::orderBy('rol_nombre')->get();

        return view('partials.roles', compact('roles'));
    }

    public function nuevoRol(Request $request)
    {
        $request->validate([
            'rol_nombre' => 'required|string|max:50|unique:rol_usuario,rol_nombre',
            '_rol_form' => 'required|in:crear',
        ]);

        DB::transaction(function () use ($request) {
            $idRol = (int) (DB::table('rol_usuario')->lockForUpdate()->max('id_rol') ?? 0) + 1;
            $rol = new RolUsuario();
            $rol->id_rol = $idRol;
            $rol->rol_nombre = $request->rol_nombre;
            $rol->save();
        });

        return redirect()->route('usuario.index')->with('status', '¡Rol registrado con éxito!');
    }

    public function modificarRol(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|exists:rol_usuario,id_rol',
            'rol_nombre' => 'required|string|max:50|unique:rol_usuario,rol_nombre,' . $request->id_rol . ',id_rol',
            '_rol_form' => 'required|in:modificar',
        ]);

        $rol = RolUsuario::findOrFail($request->id_rol);
        $rol->rol_nombre = $request->rol_nombre;
        $rol->save();

        return redirect()->route('usuario.index')->with('status', '¡Rol modificado con éxito!');
    }

    public function eliminarRol($id)
    {
        $rol = RolUsuario::findOrFail($id);

        $asignadoEnPivote = DB::table('usuario_roles')->where('fk_id_rol', $rol->id_rol)->exists();
        $asignadoEnUsuario = DB::table('usuarios')->where('id_rol_usuario', $rol->id_rol)->exists();

        if ($asignadoEnPivote || $asignadoEnUsuario) {
            return redirect()->route('usuario.index')->with('error', 'No se puede eliminar un rol asignado a usuarios.');
        }

        $rol->delete();

        return redirect()->route('usuario.index')->with('status', '¡Rol eliminado con éxito!');
    }

   public function nuevoUsuario(Request $request)
{
    $request->merge(['gmail' => strtolower(trim((string) $request->input('gmail')))]);

    $request->validate([
        'nombre'      => 'required|string|max:50',
        'gmail'       => 'required|email|max:255|ends_with:@gmail.com|unique:usuarios,gmail',
        'contrasenia' => 'required|string|min:8|confirmed',
        'roles'       => 'required|array|min:1',
        'roles.*'     => 'exists:rol_usuario,id_rol',
    ], [
        'gmail.unique'  => 'No se pueden repetir los Gmail.',
        'gmail.ends_with' => 'Debes ingresar una dirección @gmail.com.',
        'contrasenia.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'contrasenia.confirmed' => 'Las contraseñas no coinciden.',
        'roles.required' => 'Debes seleccionar un rol para el nuevo usuario.',
        'roles.min'      => 'Debes seleccionar un rol para el nuevo usuario.',
    ]);

    // 1. Crear el registro en la tabla 'usuarios'
    $usuario = Usuarios::create([
        'nombre' => $request->nombre,
        'gmail'  => $request->gmail,
        'contrasenia' => Hash::make($request->contrasenia),
    ]);

    // 2. Insertar los roles en la tabla pivote 'usuario_roles'
    if ($request->has('roles')) {
        $usuario->roles()->attach($request->roles);
    }

    return redirect()->route('usuario.index')->with('status', '¡Usuario registrado con éxito!');
}

    public function modificarUsuario(Request $request)
{
        $request->merge(['gmail' => strtolower(trim((string) $request->input('gmail')))]);

    $request->validate([
        'id'          => 'required|exists:usuarios,id',
        'nombre'      => 'required|string|max:50',
            'gmail'       => 'required|email|max:255|ends_with:@gmail.com|unique:usuarios,gmail,' . $request->id,
        'contrasenia' => 'nullable|string|min:8',
        'roles'       => 'nullable|array', // Recibe los nuevos roles seleccionados
        'roles.*'     => 'exists:rol_usuario,id_rol',
    ]);

    // 1. Actualizar datos del usuario
    $usuario = Usuarios::findOrFail($request->id);
    $usuario->nombre = $request->nombre;
    $usuario->gmail = $request->gmail;
    if ($request->filled('contrasenia')) {
        $usuario->contrasenia = Hash::make($request->contrasenia);
    }
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
