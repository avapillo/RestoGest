<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\Mesas;
use App\Models\Combo;

class MesasController extends Controller
{
   public function mostrarMesas(Request $request)
    {
        $seccionSeleccionada = $request->get('fk_id_seccion', 'todas');

        // Eager Loading para evitar problema N+1 al cargar las 4 tablas
        $querySecciones = Seccion::with([
            'mesas.pedidoActivo.detalles.producto',
            'mesas.pedidoActivo.detalles.combo'
        ]);

        if ($seccionSeleccionada !== 'todas') {
            $querySecciones->where('id_seccion', $seccionSeleccionada);
        }

        $secciones = $querySecciones->get();
        $todasSecciones = Seccion::all();
        $metodosPago = Combo::all(); // Utiliza la tabla combos para los métodos de pago

        return view('interfaz_mesa', compact('secciones', 'todasSecciones', 'seccionSeleccionada', 'metodosPago'));
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
