<?php

namespace App\Http\Controllers;

use App\Models\HistorialCaja;
use Illuminate\Http\Request;

class HistorialCajaController extends Controller
{
    // Carga los registros junto con el nombre del usuario
    public function index()
    {
        $historial = HistorialCaja::with('usuario:id,nombre')
            ->orderBy('momento_cierre', 'desc')
            ->get();

        return response()->json($historial);
    }

    // Guarda un nuevo cierre de caja usando el usuario en sesión
    public function store(Request $request)
    {
        $request->validate([
            'monto_total' => 'required|numeric'
        ]);

        $cierre = HistorialCaja::create([
            'monto_total'    => $request->monto_total,
            'momento_cierre' => now(),
            'id_usuario'     => session('usuario_id') // Toma el id guardado en LoginController
        ]);

        return response()->json([
            'mensaje' => 'Caja cerrada exitosamente',
            'data'    => $cierre
        ], 201);
    }
}
