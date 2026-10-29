<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApiCocinaController extends Controller
{
    /**
     * Devuelve los pedidos con comidas para la aplicación de cocina.
     * Las bebidas no se muestran en cocina.
     */
    public function pedidos(): JsonResponse
    {
        $pedidos = Pedido::with([
            'mesa',
            'estado',
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
                        ?? $detalle->combo->combo
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
                'fecha_pedido' => $pedido->fecha_pedido ?? null,
                'hora_pedido' => $pedido->hora_pedido,
                'monto_total' => $pedido->monto_total,
                'es_para_llevar' => $pedido->es_para_llevar,
                'fk_id_estado' => $pedido->fk_id_estado,
                'estado' => $pedido->estado->nombre ?? 'Recibido',
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

    /**
     * Cambia el estado general del pedido.
     * Estados permitidos: 1 Recibido, 2 Preparando, 3 Listo.
     */
    public function actualizarEstado(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate([
            'fk_id_estado' => [
                'required',
                'integer',
                Rule::exists('estados', 'id'),
                Rule::in([2, 3]),
            ],
        ]);

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'No se encontró el pedido.',
            ], 404);
        }

        $pedido->fk_id_estado = $datos['fk_id_estado'];
        $pedido->save();

        $pedido->load('estado');

        return response()->json([
            'mensaje' => 'Estado del pedido actualizado correctamente.',
            'id_pedido' => $pedido->id_pedido,
            'fk_id_estado' => $pedido->fk_id_estado,
            'estado' => $pedido->estado->nombre,
        ]);
    }

    public function guardar(Request $request): JsonResponse
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
                'mensaje' => 'El número de mesa no es válido.',
            ], 422);
        }

        $detalles = [];

        foreach ($datos['items'] as $item) {
            $producto = DB::table('productos')
                ->where('nombre', trim($item['producto']))
                ->first();

            if (!$producto) {
                return response()->json([
                    'mensaje' => 'No se encontró el producto: '
                        . $item['producto'],
                ], 422);
            }

            $detalles[] = [
                'id_producto' => $producto->id,
                'cantidad' => (int) $item['cantidad'],
                'subtotal' => (float) $item['precio']
                    * (int) $item['cantidad'],
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
                'fk_id_estado' => 1,
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
