<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Categoria;
use App\Models\Combo;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Seccion;
use App\Models\Mesas;

class MesasController extends Controller
{

public function mostrarMesas(Request $request)
{
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();
    $categorias = Categoria::all();
    $productos = Producto::orderBy('nombre')->get();
    $combos = Combo::orderBy('combo')->get();

    $query = Mesas::with([
        'seccion',
        'pedidos.detalles.producto',
        'pedidos.detalles.combo'
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada', 'categorias', 'productos', 'combos'));
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

    public function realizarPedido(Request $request)
    {
        $seccionSeleccionada = $request->input('fk_id_seccion');

        if ($request->expectsJson()) {
            $request->validate([
                'fk_id_seccion' => 'required|exists:seccion,id_seccion',
            ]);

            $mesas = Mesas::where('fk_id_seccion', $seccionSeleccionada)
                ->orderBy('numero_mesa')
                ->get(['id_mesa', 'numero_mesa']);

            return response()->json($mesas);
        }

        $categorias = Categoria::all();
        $secciones = Seccion::all();
        $query = Mesas::with([
            'seccion',
            'pedidos.detalles.producto',
            'pedidos.detalles.combo',
        ]);

        if (!empty($seccionSeleccionada)) {
            $query->where('fk_id_seccion', $seccionSeleccionada);
        }

        $mesas = $query->get();

        return view('interfaz_mesa', compact('secciones', 'categorias', 'mesas', 'seccionSeleccionada'));
    }

}
