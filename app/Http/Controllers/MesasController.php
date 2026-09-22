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

public function mostrarOrdenesPedidos(Request $request){
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();

    // 1. Iniciar consulta trayendo ÚNICAMENTE mesas que TENGAN pedidos
    $query = Mesas::with('pedidos')->with([
        'seccion',
        // Opcional: si manejas estado del pedido (ej. 'pendiente'), puedes filtrarlo aquí:
        'pedidos' => function ($q) {
            // $q->where('estado', 'pendiente');
        },
        'pedidos.detalles.producto',
        'pedidos.detalles.combo',
    ]);

    // 2. Filtrar por sección si el usuario eligió una
    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesasConOrdenes = $query->get();

    return view('interfaz_mesa', compact('mesasConOrdenes', 'secciones', 'seccionSeleccionada'));
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
