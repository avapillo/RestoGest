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
                    'id_registro_inicio' => DB::table('registro_pago')->max('id_registro') ?? 0,
                ]);
            } else {
                $caja->update([
                    'abierta' => true,
                    'monto_actual' => 0,
                    'momento_apertura' => now(),
                    'id_usuario' => session('usuario_id'),
                    'id_registro_inicio' => DB::table('registro_pago')->max('id_registro') ?? 0,
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
        $validated = $request->validate([
            'gastos' => ['sometimes', 'array'],
            'gastos.*.producto' => ['required', 'string', 'max:120'],
            'gastos.*.monto' => ['required', 'numeric', 'gt:0'],
        ]);
        $gastos = $validated['gastos'] ?? [];

        $resultado = DB::transaction(function () use ($adminAccess, $gastos) {
            $caja = CajaEstado::query()->lockForUpdate()->first();

            if (!$caja || !$caja->abierta) {
                return null;
            }

            $hayPedidosPendientes = DB::table('pedidos')
                ->leftJoin('estados', 'estados.id', '=', 'pedidos.fk_id_estado')
                ->where(function ($query) {
                    $query->whereNotExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('registro_pago')
                            ->whereColumn('registro_pago.fk_id_pedido', 'pedidos.id_pedido');
                    })->orWhereRaw("LOWER(TRIM(COALESCE(estados.estado, ''))) <> ?", ['listo']);
                })
                ->exists();

            if ($hayPedidosPendientes) {
                return 'pedidos_pendientes';
            }

            $montoTotal = (float) $caja->monto_actual;

            $cierre = HistorialCaja::create([
                'monto_total' => $montoTotal,
                'momento_cierre' => now(),
                'id_usuario' => session('usuario_id'),
            ]);

            if ($gastos !== []) {
                DB::table('registro_gasto')->insert(array_map(
                    static fn (array $gasto): array => [
                        'producto' => $gasto['producto'],
                        'monto' => $gasto['monto'],
                        'fk_id_historial' => $cierre->id_historial,
                    ],
                    $gastos
                ));
            }

            if ($caja->id_registro_inicio !== null) {
                DB::table('registro_pago')
                    ->where('id_registro', '>', $caja->id_registro_inicio)
                    ->whereNull('fk_id_historial')
                    ->update(['fk_id_historial' => $cierre->id_historial]);
            }

            $caja->update([
                'abierta' => false,
                'monto_actual' => 0,
                'id_registro_inicio' => null,
            ]);

            $adminAccess->record((int) session('usuario_id'), 'cierre_caja');

            return [$cierre, $montoTotal];
        });

        if (!$resultado) {
            return response()->json([
                'mensaje' => 'La caja ya está cerrada'
            ], 422);
        }

        if ($resultado === 'pedidos_pendientes') {
            return response()->json([
                'mensaje' => 'No se puede cerrar caja, hay pedidos sin cobrar o sin terminar',
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
