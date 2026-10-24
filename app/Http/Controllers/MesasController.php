<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Categoria;
use App\Models\Combo;
use App\Models\DetallePedido;
use App\Models\CajaEstado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Seccion;
use App\Models\Mesas;

class MesasController extends Controller
{

public function mostrarMesas(Request $request)
{
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();
    $categorias = Categoria::all();
    $productos = Producto::orderBy('nombre')->get();
    $combos = Combo::orderBy('combo')->get();
    $tiposPago = DB::table('tipo_pago')->orderBy('tipo_pago')->get();
    $pedidosPendientes = static fn ($query) => $query->sinCobrar();

    $query = Mesas::with([
        'seccion',
        'pedidos' => $pedidosPendientes,
        'pedidos.detalles.producto',
        'pedidos.detalles.combo'
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada', 'categorias', 'productos', 'combos', 'tiposPago'));
}

public function registrarMesa(Request $request)
{
    $datos = $request->validate([
        'numero_mesa' => [
            'required',
            'integer',
            'min:1',
            Rule::unique('mesas', 'numero_mesa')
                ->where(fn ($query) => $query->where('fk_id_seccion', $request->input('fk_id_seccion'))),
        ],
        'fk_id_seccion' => ['required', 'exists:seccion,id_seccion'],
        '_mesa_form' => ['required', 'in:crear'],
    ], [
        'numero_mesa.unique' => 'Ya existe una mesa con ese número en la sección seleccionada.',
    ]);

    Mesas::create([
        'numero_mesa' => $datos['numero_mesa'],
        'fk_id_seccion' => $datos['fk_id_seccion'],
    ]);

    return redirect()
        ->route('mesas.index', ['fk_id_seccion' => $datos['fk_id_seccion']])
        ->with('status', 'Mesa registrada correctamente.');
}

public function mostrarOrdenesPedidos(Request $request){
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();
    $pedidosPendientes = static fn ($query) => $query->sinCobrar();

    $query = Mesas::whereHas('pedidos', $pedidosPendientes)->with([
        'seccion',
        'pedidos' => $pedidosPendientes,
        'pedidos.detalles.producto',
        'pedidos.detalles.combo',
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada'));
}

    public function procesarPago(Request $request)
    {
        $datos = $request->validate([
            'id_pedido' => 'required|exists:pedidos,id_pedido',
            'fk_tipo_pago' => 'required|exists:tipo_pago,id',
        ]);

        $pedido = Pedido::with('detalles')->findOrFail($datos['id_pedido']);
        $monto = $pedido->detalles->sum('subtotal');

        if ($monto <= 0) {
            throw ValidationException::withMessages([
                'id_pedido' => 'No se puede cobrar un pedido sin productos.',
            ]);
        }

        DB::transaction(function () use ($datos, $monto) {
            $caja = CajaEstado::query()->lockForUpdate()->first();

            if (!$caja || !$caja->abierta) {
                throw ValidationException::withMessages([
                    'id_pedido' => 'Debe abrir la caja antes de procesar cobros.',
                ]);
            }

            $pedido = Pedido::where('id_pedido', $datos['id_pedido'])
                ->lockForUpdate()
                ->firstOrFail();

            if (DB::table('registro_pago')->where('fk_id_pedido', $pedido->id_pedido)->exists()) {
                throw ValidationException::withMessages([
                    'id_pedido' => 'Este pedido ya fue cobrado.',
                ]);
            }

            DB::table('registro_pago')->insert([
                'monto' => $monto,
                'fk_tipo_pago' => $datos['fk_tipo_pago'],
                'fk_id_pedido' => $pedido->id_pedido,
            ]);

            $caja->increment('monto_actual', $monto);
        });

        return redirect()->back()->with('status', 'Pago procesado exitosamente.');
    }

    public function realizarPedido(Request $request)
    {
        $seccionSeleccionada = $request->input('fk_id_seccion');

        if ($request->expectsJson()) {
            $request->validate([
                'fk_id_seccion' => 'required|exists:seccion,id_seccion',
            ]);

            $mesas = Mesas::where('fk_id_seccion', $seccionSeleccionada)
                ->orderBy('numero_mesa')
                ->get(['id_mesa', 'numero_mesa']);

            return response()->json($mesas);
        }

        $datos = $request->validate([
            'fk_id_seccion' => ['required', 'exists:seccion,id_seccion'],
            'fk_id_mesa' => [
                'required',
                Rule::exists('mesas', 'id_mesa')->where('fk_id_seccion', $seccionSeleccionada),
            ],
            'es_para_llevar' => ['required', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fk_id_categoria' => ['required', 'exists:categoria,id'],
            'items.*.fk_id_producto' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $productos = Producto::whereIn(
            'id',
            collect($datos['items'])->pluck('fk_id_producto')->unique()
        )->get()->keyBy('id');

        foreach ($datos['items'] as $indice => $item) {
            $producto = $productos->get($item['fk_id_producto']);

            if ((int) $producto->fk_id_categoria !== (int) $item['fk_id_categoria']) {
                throw ValidationException::withMessages([
                    "items.$indice.fk_id_producto" => 'El producto no pertenece a la categoría seleccionada.',
                ]);
            }
        }

        DB::transaction(function () use ($datos, $productos) {
            $pedido = Pedido::create([
                'fk_id_mesa' => $datos['fk_id_mesa'],
                'es_para_llevar' => $datos['es_para_llevar'],
            ]);

            foreach ($datos['items'] as $item) {
                $producto = $productos->get($item['fk_id_producto']);
                $precioUnitario = (int) $producto->precio;

                $pedido->detalles()->create([
                    'fk_id_producto' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $precioUnitario * $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('mesas.index', ['fk_id_seccion' => $seccionSeleccionada])
            ->with('status', 'Pedido registrado correctamente.');
    }

    public function modificarPedido(Request $request, $id_pedido)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id_pedido);

        $datos = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.fk_id_categoria' => ['required', 'exists:categoria,id'],
            'items.*.fk_id_producto' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $productos = Producto::whereIn(
            'id',
            collect($datos['items'])->pluck('fk_id_producto')->unique()
        )->get()->keyBy('id');

        foreach ($datos['items'] as $indice => $item) {
            $producto = $productos->get($item['fk_id_producto']);

            if ((int) $producto->fk_id_categoria !== (int) $item['fk_id_categoria']) {
                throw ValidationException::withMessages([
                    "items.$indice.fk_id_producto" => 'El producto no pertenece a la categoría seleccionada.',
                ]);
            }
        }

        DB::transaction(function () use ($pedido, $datos, $productos) {
            $pedido = Pedido::where('id_pedido', $pedido->id_pedido)
                ->lockForUpdate()
                ->firstOrFail();

            if (DB::table('registro_pago')->where('fk_id_pedido', $pedido->id_pedido)->exists()) {
                throw ValidationException::withMessages([
                    'id_pedido' => 'No se puede modificar un pedido que ya fue cobrado.',
                ]);
            }

            // Eliminar los detalles existentes
            $pedido->detalles()->delete();

            // Crear nuevos detalles
            foreach ($datos['items'] as $item) {
                $producto = $productos->get($item['fk_id_producto']);
                $precioUnitario = (int) $producto->precio;

                $pedido->detalles()->create([
                    'fk_id_producto' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $precioUnitario * $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('mesas.index', ['fk_id_seccion' => $pedido->mesa->fk_id_seccion])
            ->with('status', 'Pedido modificado correctamente.');
    }

}
