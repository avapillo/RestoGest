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
            'monto_inicial' => (float) $caja->monto_inicial,
            'mensaje' => $caja->abierta ? 'Caja abierta.' : 'La caja está cerrada.',
        ]);
    }

    // Abrir caja
    public function abrir(Request $request, AdminAccess $adminAccess)
    {
        $validated = $request->validate([
            'monto_inicial' => ['sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);
        $montoInicial = (float) ($validated['monto_inicial'] ?? 0);

        $caja = DB::transaction(function () use ($adminAccess, $montoInicial) {
            $caja = CajaEstado::query()->lockForUpdate()->first();

            if ($caja?->abierta) {
                return null;
            }

            if (!$caja) {
                $caja = CajaEstado::create([
                    'abierta' => true,
                    'monto_actual' => 0,
                    'monto_inicial' => $montoInicial,
                    'momento_apertura' => now(),
                    'id_usuario' => session('usuario_id'),
                    'id_registro_inicio' => DB::table('registro_pago')->max('id_registro') ?? 0,
                    'id_pedido_inicio' => DB::table('pedidos')->max('id_pedido') ?? 0,
                ]);
            } else {
                $caja->update([
                    'abierta' => true,
                    'monto_actual' => 0,
                    'monto_inicial' => $montoInicial,
                    'momento_apertura' => now(),
                    'id_usuario' => session('usuario_id'),
                    'id_registro_inicio' => DB::table('registro_pago')->max('id_registro') ?? 0,
                    'id_pedido_inicio' => DB::table('pedidos')->max('id_pedido') ?? 0,
                ]);
            }

            $adminAccess->record((int) session('usuario_id'), 'apertura_caja');

            return $caja;
        });

        if (!$caja) {
            return response()->json([
                'mensaje' => 'La caja ya está abierta.',
            ], 422);
        }

        return response()->json([
            'mensaje' => 'Caja abierta correctamente',
            'abierta' => true,
            'monto_actual' => 0,
            'monto_inicial' => $montoInicial,
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
                ->where('pedidos.id_pedido', '>', $caja->id_pedido_inicio ?? 0)
                ->where(function ($query) {
                    $query->whereNotExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('registro_pago')
                            ->whereColumn('registro_pago.fk_id_pedido', 'pedidos.id_pedido');
                    })->orWhereRaw("LOWER(TRIM(COALESCE(estados.estado, ''))) <> ?", ['listo']);
                })
                ->where(function ($query) {
                    $query->whereNotExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('detalle_pedido')
                            ->whereColumn('detalle_pedido.fk_id_pedido', 'pedidos.id_pedido');
                    })->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('detalle_pedido')
                            ->leftJoin('productos', 'productos.id', '=', 'detalle_pedido.fk_id_producto')
                            ->leftJoin('categoria', 'categoria.id', '=', 'productos.fk_id_categoria')
                            ->whereColumn('detalle_pedido.fk_id_pedido', 'pedidos.id_pedido')
                            ->where(function ($query) {
                                $query->whereNull('detalle_pedido.fk_id_producto')
                                        ->orWhereRaw(
                                            "LOWER(TRIM(COALESCE(categoria.categoria, ''))) NOT LIKE ?",
                                            ['%bebida%']
                                        );
                            });
                    });
                })
                ->exists();

            if ($hayPedidosPendientes) {
                return 'pedidos_pendientes';
            }

            $montoTotal = (float) $caja->monto_actual;
            $montoInicial = (float) $caja->monto_inicial;
            $montoGastos = array_sum(array_map(
                static fn (array $gasto): int => (int) round((float) $gasto['monto'] * 100),
                $gastos
            ));
            $montoCierreCentavos = (int) round($montoTotal * 100)
                + (int) round($montoInicial * 100)
                - $montoGastos;
            $montoCierre = $montoCierreCentavos / 100;

            $cierre = HistorialCaja::create([
                'monto_total' => $montoTotal,
                'monto_inicial' => $montoInicial,
                'monto_cierre' => $montoCierre,
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
                'monto_inicial' => 0,
                'id_registro_inicio' => null,
                'id_pedido_inicio' => null,
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
