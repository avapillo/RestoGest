<?php

namespace App\Http\Controllers;

use App\Models\Mesas;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeccionController extends Controller
{
    public function mostrarSecciones()
    {
        $secciones = Seccion::orderBy('seccion')->get();

        return view('partials.secciones', compact('secciones'));
    }

    public function registrarSeccion(Request $request)
    {
        $datos = $request->validate([
            'seccion' => 'required|string|max:60|unique:seccion,seccion',
            '_seccion_form' => 'required|in:crear',
        ]);

        Seccion::create(['seccion' => $datos['seccion']]);

        return redirect()->route('mesas.index')->with('status', '¡Sección registrada con éxito!');
    }

    public function modificarSeccion(Request $request)
    {
        $request->validate([
            'id_seccion' => 'required|exists:seccion,id_seccion',
            '_seccion_form' => 'required|in:modificar',
        ]);

        $datos = $request->validate([
            'seccion' => [
                'required',
                'string',
                'max:60',
                Rule::unique('seccion', 'seccion')->ignore($request->id_seccion, 'id_seccion'),
            ],
        ]);

        $seccion = Seccion::findOrFail($request->id_seccion);
        $seccion->update(['seccion' => $datos['seccion']]);

        return redirect()->route('mesas.index')->with('status', '¡Sección modificada con éxito!');
    }

    public function eliminarSeccion($id)
    {
        $seccion = Seccion::findOrFail($id);

        if (Mesas::where('fk_id_seccion', $seccion->id_seccion)->exists()) {
            return redirect()->route('mesas.index')->with('error', 'No se puede eliminar una sección que tiene mesas asignadas.');
        }

        $seccion->delete();

        return redirect()->route('mesas.index')->with('status', '¡Sección eliminada con éxito!');
    }
}
