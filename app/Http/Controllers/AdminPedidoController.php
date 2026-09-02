<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class MesasController extends Controller
{
    // Muestra todas las tarjetas de pedidos activos en el Admin
    public function muestraPedido()
    {
        // 'with' hace la consulta optimizada (Eager Loading)


        $pedidos = Pedido::with(['detalles.producto', 'detalles.combo', 'mesa'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('interfaz_mesa', compact('pedidos'));
    }

    // Muestra una sola tarjeta/pedido por ID
    public function show($id)
    {
        $pedido = Pedido::with(['detalles.producto', 'detalles.combo', 'mesa'])
                    ->findOrFail($id);

        return view('interfaz_mesa', compact('pedido'));
    }
}
