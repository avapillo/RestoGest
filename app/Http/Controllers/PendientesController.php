<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendiente;

class PendientesController extends Controller
{
    // Mostrar la pantalla de pendientes
    public function index()
    {
        $pendientes = Pendiente::orderBy('created_at', 'desc')->get();

        return view('interfaz_pendientes', compact('pendientes'));
    }

    // Registrar un pendiente
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'regex:/^\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*$/u'],
            'monto' => 'required|numeric|min:0.01',
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
        ]);

        Pendiente::create($datos);

        return redirect()->route('pendientes.index')
            ->with('status', 'Pendiente agregado correctamente.');
    }

    // Modificar un pendiente
    public function update(Request $request, $id)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'regex:/^\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*$/u'],
            'monto' => 'required|numeric|min:0.01',
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
        ]);

        $pendiente = Pendiente::findOrFail($id);
        $pendiente->update($datos);

        return redirect()->route('pendientes.index')
            ->with('status', 'Pendiente modificado correctamente.');
    }

    // Eliminar un pendiente
    public function destroy($id)
    {
        $pendiente = Pendiente::findOrFail($id);
        $pendiente->delete();

        return redirect()->route('pendientes.index')
            ->with('status', 'Pendiente eliminado correctamente.');
    }
}