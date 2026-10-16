<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiCocinaController extends Controller
{
    /**
     * Devuelve los pedidos con comidas para la aplicación de cocina.
     * Las bebidas siguen guardadas en la base de datos,
     * pero no se incluyen en la respuesta de este endpoint.
     */
    public function pedidos(): JsonResponse
    {
        $pedidos = Pedido::with([
            'mesa',
            'detalles.producto.categoria',
            'detalles.combo',
        ])
        ->orderBy('hora_pedido', 'desc')
        ->get()
        ->map(function ($pedido) {
            $detalles = $pedido->detalles
                ->filter(function ($detalle) {
                    // Excluir bebidas solamente de la respuesta de cocina.
                    if ($detalle->producto) {
                        return (int) $detalle->producto->fk_id_categoria !== 4;
                    }

                    // Conservar los combos.
                    return true;
                })
                ->values()
                ->map(function ($detalle) {
                    $nombre = $detalle->producto->nombre
                        ?? $detalle->combo->nombre
                        ?? ('Combo #' . $detalle->fk_id_combo);

                    return [
                        'id_detalle' => $detalle->id_detalle,
                        'fk_id_producto' => $detalle->fk_id_producto,
                        'fk_id_combo' => $detalle->fk_id_combo,
                        'nombre' => $nombre,
                        'producto' => $nombre,
                        'cantidad' => $detalle->cantidad,
                        'subtotal' => $detalle->subtotal,
                        'descripcion' => $detalle->descripcion,
                    ];
                });

            return [
                'id_pedido' => $pedido->id_pedido,
                'mesa' => $pedido->fk_id_mesa,
                'fecha_pedido' => $pedido->fecha_pedido,
                'monto_total' => $pedido->monto_total,
                'es_para_llevar' => $pedido->es_para_llevar,
                'detalles' => $detalles,
            ];
        })
        // No mostrar pedidos que solo tengan bebidas en cocina.
        ->filter(function ($pedido) {
            return $pedido['detalles']->isNotEmpty();
        })
        ->values();

        return response()->json($pedidos);
        }
        
public function guardar(Request $request)
{
    $datos = $request->validate([
        'mesa' => ['required', 'string'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.producto' => ['required', 'string'],
        'items.*.cantidad' => ['required', 'integer', 'min:1'],
        'items.*.precio' => ['required', 'numeric', 'min:0'],
        'items.*.descripcion' => ['nullable', 'string', 'max:255'],
    ]);

    $numeroMesa = (int) preg_replace('/\D/', '', $datos['mesa']);

    if ($numeroMesa < 1) {
        return response()->json([
            'mensaje' => 'El número de mesa no es válido.'
        ], 422);
    }

    // Verificar todos los productos antes de guardar.
    $detalles = [];

    foreach ($datos['items'] as $item) {
        $producto = DB::table('productos')
            ->where('nombre', trim($item['producto']))
            ->first();

        if (!$producto) {
            return response()->json([
                'mensaje' => 'No se encontró el producto: '
                    . $item['producto']
            ], 422);
        }

        $detalles[] = [
            'id_producto' => $producto->id,
            'cantidad' => (int) $item['cantidad'],
            'subtotal' => (int) round((float) $item['precio']),
            'descripcion' => $item['descripcion'] ?? '',
        ];
    }

    $total = array_sum(array_column($detalles, 'subtotal'));

    $idPedido = DB::transaction(function () use (
        $numeroMesa,
        $detalles,
        $total
    ) {
        $mesa = DB::table('mesas')
            ->where('numero_mesa', $numeroMesa)
            ->first();

        // Crear la mesa solo si todavía no existe.
        if (!$mesa) {
            $idMesa = DB::table('mesas')->insertGetId([
                'numero_mesa' => $numeroMesa,
                'fk_id_seccion' => null,
            ]);
        } else {
            $idMesa = $mesa->id_mesa;
        }

        $id = DB::table('pedidos')->insertGetId([
            'monto_total' => $total,
            'hora_pedido' => now()->format('H:i:s'),
            'fk_id_mesa' => $idMesa,
            'fk_id_tipo_pago' => null,
            'es_para_llevar' => 0,
        ]);

        foreach ($detalles as $detalle) {
            DB::table('detalle_pedido')->insert([
                'fk_id_pedido' => $id,
                'fk_id_producto' => $detalle['id_producto'],
                'fk_id_combo' => null,
                'cantidad' => $detalle['cantidad'],
                'subtotal' => $detalle['subtotal'],
                'descripcion' => $detalle['descripcion'],
            ]);
        }

        return $id;
    });

    return response()->json([
        'mensaje' => 'Pedido enviado a Cocina correctamente.',
        'id_pedido' => $idPedido,
        'total' => $total,
    ], 201);
}

}
