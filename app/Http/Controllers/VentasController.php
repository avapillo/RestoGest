<?php

namespace App\Http\Controllers;

use App\Models\Ventas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{
    public function index()
    {
        $tiposPago = DB::table('tipo_pago')
            ->select('id', 'tipo_pago')
            ->orderBy('tipo_pago')
            ->get();

        return view('interfaz_ventas', compact('tiposPago'));
    }

    public function buscar(Request $request)
    {
        $reglas = [
            'id_registro' => 'sometimes|nullable|integer|min:1',
            'monto' => 'sometimes|nullable|numeric|min:0',
            'fk_tipo_pago' => 'sometimes|nullable|integer|exists:tipo_pago,id',
            'fecha_pago' => 'sometimes|nullable|date',
            'fecha_desde' => 'sometimes|nullable|date',
            'fecha_hasta' => 'sometimes|nullable|date',
            'fk_id_historial' => 'sometimes|nullable|integer|exists:historial_caja,id_historial',
            'fk_id_pedido' => 'sometimes|nullable|integer|exists:pedidos,id_pedido',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $reglas['fecha_hasta'] .= '|after_or_equal:fecha_desde';
        }

        $filtros = $request->validate($reglas);

        $ventas = Ventas::query()->buscar($filtros);

        if (!empty($filtros['fecha_desde'])) {
            $ventas->whereDate('fecha_pago', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $ventas->whereDate('fecha_pago', '<=', $filtros['fecha_hasta']);
        }

        $resultados = $ventas
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id_registro')
            ->paginate($filtros['per_page'] ?? 15)
            ->withQueryString();

        return response()->json($resultados);
    }
}
