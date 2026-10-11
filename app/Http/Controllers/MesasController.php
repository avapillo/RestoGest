<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Categoria;
use App\Models\Combo;
use App\Models\DetallePedido;
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

    $query = Mesas::with([
        'seccion',
        'pedidos.detalles.producto',
        'pedidos.detalles.combo'
    ]);

    if ($seccionSeleccionada !== 'todas' && !empty($seccionSeleccionada)) {
        $query->where('fk_id_seccion', $seccionSeleccionada);
    }

    $mesas = $query->get();

    return view('interfaz_mesa', compact('mesas', 'secciones', 'seccionSeleccionada', 'categorias', 'productos', 'combos'));
}

public function mostrarOrdenesPedidos(Request $request){
    $seccionSeleccionada = $request->query('fk_id_seccion', 'todas');
    $secciones = Seccion::all();

    $query = Mesas::whereHas('pedidos')->with([
        'seccion',
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
        $request->validate([
            'id_pedido' => 'required|exists:pedidos,id_pedido',
            'fk_id_tipo_pago' => 'required|exists:combos,id',
        ]);

        $pedido = Pedido::findOrFail($request->id_pedido);
        $pedido->fk_id_tipo_pago = $request->fk_id_tipo_pago;
        $pedido->save();

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

}
