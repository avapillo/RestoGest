<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\JsonResponse;

class ApiCocinaController extends Controller
{
    /**
     * Obtener los pedidos para la aplicación de cocina.
     * Excluye las bebidas (categoría 4), sin eliminarlas
     * de la base de datos ni modificar los pedidos originales.
     */
    public function pedidos(): JsonResponse
    {
        $pedidos = Pedido::with([
            'mesa',
            'detalles.producto.categoria',
            'detalles.combo',
        ])
        ->orderBy('hora_pedido', 'asc')
        ->get();

        $pedidos->each(function ($pedido) {
            $detallesFiltrados = $pedido->detalles
                ->filter(function ($detalle) {
                    // Conservar los detalles que corresponden
                    // solamente a combos.
                    if (!$detalle->producto) {
                        return true;
                    }

                    // Excluir las bebidas de categoría 4.
                    return (int) $detalle->producto->fk_id_categoria !== 4;
                })
                ->values();

            $pedido->setRelation(
                'detalles',
                $detallesFiltrados
            );
        });

        return response()->json($pedidos);
    }
}
