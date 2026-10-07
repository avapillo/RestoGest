<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Seccion;
use App\Models\Mesas;

class MesasController extends Controller
{

public function mostrarMesas(Request $request)
{
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();

    $query = Mesas::with([
        'seccion',
        'pedidos.detalles.producto',
        'pedidos.detalles.combo'
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada'));
}

public function mostrarOrdenesPedidos(Request $request){
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();

    $query = Mesas::whereHas('pedidos')->with([
        'seccion',
        'pedidos.detalles.producto',
        'pedidos.detalles.combo',
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

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
