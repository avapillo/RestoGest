<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\Producto;
use App\Models\Mesa;

class SeccionController extends Controller
{
    public function secciones(Request $request)
    {
        // Obtener la sección seleccionada
        $seccionSeleccionada = $request->query('id_seccion', 'todas');

        // Obtener todas las secciones
        $secciones = Seccion::all();

        // Obtener productos
        if ($seccionSeleccionada === 'todas') {
            $mesas = Mesas::with('seccion')->get();
        } else {
            $mesas = Mesas::with('seccion')
                ->where('fk_id_seccion', $seccionSeleccionada)
                ->get();

        }

        // Obtener mesas
        $mesas = Mesa::all();

        return view(
            'interfaz_mesa',
            compact(
                'mesas',
                'secciones',
                'seccionSeleccionada',
                'productos'
            )
        );
    }
}