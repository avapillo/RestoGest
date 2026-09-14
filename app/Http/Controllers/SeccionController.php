<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD

class SeccionController extends Controller
{
    //
=======
use App\Models\Seccion;
use App\Models\Producto;
use App\Models\Mesa; // Asegúrate de importar el modelo Mesa si usas $mesas

class SeccionController extends Controller
{
    public function mostrar(Request $request)
    {
        // 1. Obtener el parámetro de la URL (si no viene nada, asigna 'todas')
        $seccionSeleccionada = $request->query('id_seccion', 'todas');

        // 2. Traer todas las secciones para renderizar los botones del filtro
        $secciones = Seccion::all();

        // 3. Traer las mesas o productos filtrados según la sección
        if ($seccionSeleccionada === 'todas') {
            $productos = Producto::with('seccion')->get();
        } else {
            $productos = Producto::with('seccion')
                ->where('fk_id_seccion', $seccionSeleccionada)
                ->get();
        }

        // Obtener la lista de mesas para la pantalla
        $mesas = Mesa::all();

        // 4. Pasar las variables exactas a la vista 'interfaz_mesa'
        return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada', 'productos'));
    }
>>>>>>> 5a28dd570ec2d80539b168a3ca99c3ba7643ef0f
}
