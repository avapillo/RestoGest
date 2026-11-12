<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VentasController extends Controller
{
    public function index()
    {
        $tiposPago = DB::table('tipo_pago')
            ->select('id', 'tipo_pago')
            ->orderBy('tipo_pago')
            ->get();

        $categorias = Categoria::query()
            ->select('id', 'categoria')
            ->orderBy('categoria')
            ->get();

        $productos = DB::table('productos')
            ->select('id', 'nombre', 'fk_id_categoria')
            ->orderBy('nombre')
            ->get();

        return view('interfaz_ventas', compact('tiposPago', 'categorias', 'productos'));
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
                'pago.id_registro',
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

    public function detalle(int $id)
    {
        $venta = DB::table('registro_pago as pago')
            ->leftJoin('pedidos as pedido', 'pedido.id_pedido', '=', 'pago.fk_id_pedido')
            ->leftJoin('mesas as mesa', 'mesa.id_mesa', '=', 'pedido.fk_id_mesa')
            ->leftJoin('seccion as seccion', 'seccion.id_seccion', '=', 'mesa.fk_id_seccion')
            ->where('pago.id_registro', $id)
            ->first([
                'pago.fk_id_pedido',
                'pedido.es_para_llevar',
                'mesa.numero_mesa',
                'seccion.seccion',
            ]);

        abort_if($venta === null, 404);

        $productos = collect();

        if ($venta->fk_id_pedido !== null) {
            $detalles = DB::table('detalle_pedido as detalle')
                ->leftJoin('productos as producto', 'producto.id', '=', 'detalle.fk_id_producto')
                ->where('detalle.fk_id_pedido', $venta->fk_id_pedido)
                ->select([
                    'detalle.cantidad',
                    'detalle.precio_unitario',
                    'detalle.subtotal',
                    'detalle.descripcion',
                    'producto.nombre as producto_nombre',
                ]);

            $tieneCombos = Schema::hasColumn('detalle_pedido', 'fk_id_combo');

            if ($tieneCombos) {
                $detalles->addSelect('detalle.fk_id_combo');
            }

            if ($tieneCombos && Schema::hasTable('combos')) {
                $detalles->leftJoin('combos as combo', 'combo.id', '=', 'detalle.fk_id_combo')
                    ->addSelect('combo.combo as combo_nombre');
            }

            $productos = $detalles->get()->map(function ($detalle) {
                $comboId = $detalle->fk_id_combo ?? null;
                $nombre = $detalle->producto_nombre
                    ?? ($detalle->combo_nombre ?? null)
                    ?? $detalle->descripcion
                    ?? ($comboId !== null ? 'Combo '.$comboId : 'Producto');

                $cantidad = (int) ($detalle->cantidad ?? 0);
                $precio = $detalle->precio_unitario;

                if ($precio === null && $detalle->subtotal !== null && $cantidad > 0) {
                    $precio = (float) $detalle->subtotal / $cantidad;
                }

                return [
                    'nombre' => $nombre,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                ];
            });
        }

        return response()->json([
            'seccion' => $venta->seccion,
            'numero_mesa' => $venta->numero_mesa,
            'para_llevar' => (bool) $venta->es_para_llevar && $venta->numero_mesa === null,
            'productos' => $productos,
        ]);
    }

    public function productosMasVendidos(Request $request)
    {
        $reglas = [
            'categoria_id' => 'sometimes|nullable|integer|exists:categoria,id',
            'producto_id' => 'sometimes|nullable|integer|exists:productos,id',
            'fecha_desde' => 'sometimes|nullable|date',
            'fecha_hasta' => 'sometimes|nullable|date',
        ];

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $reglas['fecha_hasta'] .= '|after_or_equal:fecha_desde';
        }

        $filtros = $request->validate($reglas);

        $productos = DB::table('detalle_pedido as detalle')
            ->join('productos as producto', 'producto.id', '=', 'detalle.fk_id_producto')
            ->join('registro_pago as pago', 'pago.fk_id_pedido', '=', 'detalle.fk_id_pedido')
            ->select('producto.id', 'producto.nombre')
            ->selectRaw('SUM(detalle.cantidad) as unidades_vendidas')
            ->whereNotNull('detalle.fk_id_producto')
            ->when(!empty($filtros['categoria_id']), function ($query) use ($filtros) {
                $query->where('producto.fk_id_categoria', $filtros['categoria_id']);
            })
            ->when(!empty($filtros['producto_id']), function ($query) use ($filtros) {
                $query->where('producto.id', $filtros['producto_id']);
            })
            ->when(!empty($filtros['fecha_desde']), function ($query) use ($filtros) {
                $query->whereDate('pago.fecha_pago', '>=', $filtros['fecha_desde']);
            })
            ->when(!empty($filtros['fecha_hasta']), function ($query) use ($filtros) {
                $query->whereDate('pago.fecha_pago', '<=', $filtros['fecha_hasta']);
            })
            ->groupBy('producto.id', 'producto.nombre')
            ->orderByDesc('unidades_vendidas')
            ->orderBy('producto.nombre')
            ->limit(10)
            ->get();

        return response()->json($productos);
    }
}
