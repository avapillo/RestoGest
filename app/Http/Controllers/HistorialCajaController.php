<?php

namespace App\Http\Controllers;

use App\Models\HistorialCaja;
use App\Models\CajaEstado;
use Illuminate\Http\Request;

class HistorialCajaController extends Controller
{
    // Mostrar historial
    public function index()
    {
        $historial = HistorialCaja::with('usuario:id,nombre')
            ->orderBy('momento_cierre', 'desc')
            ->get();

        return response()->json($historial);
    }

    // Obtener estado actual de la caja
    public function estado()
    {
        $caja = CajaEstado::first();

        if (!$caja) {
            $caja = CajaEstado::create([
                'abierta' => false,
                'monto_actual' => 0,
            ]);
        }

        return response()->json([
            'abierta' => $caja->abierta,
            'monto_actual' => (float) $caja->monto_actual,
        ]);
    }

    // Abrir caja
    public function abrir()
    {
        $caja = CajaEstado::first();

        if (!$caja) {
            $caja = CajaEstado::create([
                'abierta' => true,
                'monto_actual' => 0,
                'momento_apertura' => now(),
                'id_usuario' => session('usuario_id'),
            ]);
        } else {
            $caja->update([
                'abierta' => true,
                'monto_actual' => 0,
                'momento_apertura' => now(),
                'id_usuario' => session('usuario_id'),
            ]);
        }

        return response()->json([
            'mensaje' => 'Caja abierta correctamente',
            'abierta' => true,
            'monto_actual' => 0,
        ]);
    }

    // Registrar un nuevo pago
    public function registrarPago(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
        ]);

        $caja = CajaEstado::first();

        if (!$caja || !$caja->abierta) {
            return response()->json([
                'mensaje' => 'La caja está cerrada'
            ], 422);
        }

        $nuevoMonto = (float) $caja->monto_actual + (float) $request->monto;

        $caja->update([
            'monto_actual' => $nuevoMonto,
        ]);

        return response()->json([
            'mensaje' => 'Pago registrado correctamente',
            'monto_actual' => $nuevoMonto,
        ]);
    }

    // Cerrar caja
    public function store(Request $request)
    {
        $caja = CajaEstado::first();

        if (!$caja || !$caja->abierta) {
            return response()->json([
                'mensaje' => 'La caja ya está cerrada'
            ], 422);
        }

        $montoTotal = (float) $caja->monto_actual;

        $cierre = HistorialCaja::create([
            'monto_total' => $montoTotal,
            'momento_cierre' => now(),
            'id_usuario' => session('usuario_id'),
        ]);

        $caja->update([
            'abierta' => false,
            'monto_actual' => 0,
        ]);

        return response()->json([
            'mensaje' => 'Caja cerrada exitosamente',
            'data' => $cierre,
            'abierta' => false,
            'monto_actual' => 0,
        ], 201);
    }
}