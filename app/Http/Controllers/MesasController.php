<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\Mesas;
use App\Models\Combo;
use App\Models\Pedido; // Importado para evitar error en procesarPago

class MesasController extends Controller
{
    public function mostrarMesas(Request $request)
    {
        $seccionSeleccionada = $request->get('fk_id_seccion', 'todas');

        // Eager Loading para evitar problema N+1 al cargar relaciones
        $querySecciones = Seccion::with([
            'mesas.pedidoActivo.detalles.producto',
            'mesas.pedidoActivo.detalles.combo'
        ]);

        // Verificamos que el filtro no sea 'todas', ni null, ni una cadena vacía
        if ($seccionSeleccionada && $seccionSeleccionada !== 'todas') {
            $querySecciones->where('id_seccion', $seccionSeleccionada);
        }

        $secciones = $querySecciones->get();
        $todasSecciones = Seccion::all();
        $metodosPago = Combo::all(); // Métodos de pago desde la tabla combos

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

        // Opcional: si manejas un estado del pedido al pagar (ej. 'Pagado' o 'Finalizado')
        // $pedido->estado = 'Pagado';

        $pedido->save();

        return redirect()->back()->with('status', 'Pago procesado exitosamente.');
    }
}
