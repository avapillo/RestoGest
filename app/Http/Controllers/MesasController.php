<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\Mesas;

class MesasController extends Controller
{
    public function mostrarMesas(Request $request)
    {
        $seccionSelecionada = $request->query('id_seccion');

        $seccion = seccion::all();
        $mesas = Mesas::all();

        if($seccionSelecionada === 'id_seccion'){
            $mesas = Mesas::witch('seccion')
            ->where('fk_id_seccion', $seccionSelecionada)
            ->get();
        }

        return view('interfaz_mesa', compact('mesas', 'seccion', 'seccionSelecionada'));
    }


}
