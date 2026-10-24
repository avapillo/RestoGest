<?php

namespace App\Http\Controllers;

use App\Models\HistorialCaja;
use App\Models\CajaEstado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\AdminAccess;

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
            'mensaje' => $caja->abierta ? 'Caja abierta.' : 'La caja está cerrada.',
        ]);
    }

    // Abrir caja
    public function abrir(AdminAccess $adminAccess)
    {
        $caja = DB::transaction(function () use ($adminAccess) {
            $caja = CajaEstado::query()->lockForUpdate()->first();

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

            $adminAccess->record((int) session('usuario_id'), 'apertura_caja');

            return $caja;
        });

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
    public function store(Request $request, AdminAccess $adminAccess)
    {
        $resultado = DB::transaction(function () use ($adminAccess) {
            $caja = CajaEstado::query()->lockForUpdate()->first();

            if (!$caja || !$caja->abierta) {
                return null;
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

            $adminAccess->record((int) session('usuario_id'), 'cierre_caja');

            return [$cierre, $montoTotal];
        });

        if (!$resultado) {
            return response()->json([
                'mensaje' => 'La caja ya está cerrada'
            ], 422);
        }

        [$cierre, $montoTotal] = $resultado;

        return response()->json([
            'mensaje' => 'Caja cerrada exitosamente',
            'data' => $cierre,
            'abierta' => false,
            'monto_actual' => 0,
        ], 201);
    }

}
