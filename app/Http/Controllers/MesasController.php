<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedio;
use App\Models\Seccion;
use App\Models\Mesas;
use App\Models\Combo;

class MesasController extends Controller
{

public function mostrarMesas(Request $request)
{
    // Asegúrate de que el nombre del parámetro coincida con el que envía tu JS/Blade ('seccion' o 'fk_id_seccion')
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');

    // 1. Cargar las secciones para la barra/botones de filtros
    $secciones = Seccion::all();

    // 2. Aplicar exactamente la misma estructura de Producto
    if ($seccionSeleccionada === 'todas') {
        $mesas = Mesas::with('seccion')->get();
    } else {
        $mesas = Mesas::with('seccion')
            ->where('fk_id_seccion', $seccionSeleccionada)
            ->get();
    }

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada'));
}

    public function procesarPago(Request $request)
    {
        $request->validate([
            'id_pedido' => 'required|exists:pedidos,id_pedido',
            'fk_id_tipo_pago' => 'required|exists:combos,id',
        ]);

        $pedido = Pedido::findOrFail($request->id_pedido);
        $pedido->fk_id_tipo_pago = $request->fk_id_tipo_pago;
        $pedido->save();

        return redirect()->back()->with('status', 'Pago procesado exitosamente.');
    }

}
