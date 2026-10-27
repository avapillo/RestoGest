<?php

namespace App\Http\Controllers;

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
            'fk_tipo_pago' => 'sometimes|nullable|integer|exists:tipo_pago,id',
            'fecha_desde' => 'sometimes|nullable|date',
            'fecha_hasta' => 'sometimes|nullable|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $reglas['fecha_hasta'] .= '|after_or_equal:fecha_desde';
        }

        $filtros = $request->validate($reglas);

        $ventas = DB::table('registro_pago as pago')
            ->leftJoin('tipo_pago as tipo', 'tipo.id', '=', 'pago.fk_tipo_pago')
            ->select([
                'pago.fecha_pago',
                'pago.monto',
                'tipo.tipo_pago as metodo_pago',
            ]);

        if (!empty($filtros['fk_tipo_pago'])) {
            $ventas->where('pago.fk_tipo_pago', $filtros['fk_tipo_pago']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $ventas->whereDate('pago.fecha_pago', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $ventas->whereDate('pago.fecha_pago', '<=', $filtros['fecha_hasta']);
        }

        $resultados = $ventas
            ->orderByDesc('pago.fecha_pago')
            ->orderByDesc('pago.id_registro')
            ->paginate($filtros['per_page'] ?? 15)
            ->withQueryString();

        return response()->json($resultados);
    }
}
