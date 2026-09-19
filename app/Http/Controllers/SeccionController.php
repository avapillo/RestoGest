<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;

class SeccionController extends Controller
{
    public function secciones(Request $request)
    {

    $seccionSeleccionada = $request->query('seccion', 'todas');

        $secciones = Seccion::all();

        if ($seccionSeleccionada === 'todas') {
            $mesas = Mesas::with('seccion')->get();
        } else {
            $mesas = Mesas::with('seccion')
                ->where('fk_id_seccion', $seccionSeleccionada)
                ->get();

        }

        return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada'));
    }
}
