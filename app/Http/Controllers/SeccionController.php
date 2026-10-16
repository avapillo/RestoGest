<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\Producto;
use App\Models\Mesa;

class SeccionController extends Controller
{
    public function mostrar(Request $request)
    {
        // Obtener la sección seleccionada.
        $seccionSeleccionada = $request->query('id_seccion', 'todas');

        // Obtener todas las secciones.
        $secciones = Seccion::all();

        // Obtener los productos según la sección seleccionada.
        if ($seccionSeleccionada === 'todas') {
            $productos = Producto::with('seccion')->get();
        } else {
            $productos = Producto::with('seccion')
                ->where('fk_id_seccion', $seccionSeleccionada)
                ->get();
        }

        // Obtener todas las mesas.
        $mesas = Mesa::all();

        // Mostrar la vista.
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