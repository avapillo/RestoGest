<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function mostrarProducto(Request $request)
    {
        $categoriaSeleccionada = $request->query('fk_id_categoria', 'todas');

        // Traemos las categorías de la BD
        $categorias = Categoria::all();

        // Carga de productos con su relación
        if ($categoriaSeleccionada === 'todas') {
            $productos = Producto::with('categoria')->get();
        } else {
            $productos = Producto::with('categoria')
                ->where('fk_id_categoria', $categoriaSeleccionada)
                ->get();
        }

        return view('interfaz_producto', compact('productos', 'categorias', 'categoriaSeleccionada'));
    }

    public function registroProducto(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'precio'          => 'required|integer|min:0',
            'fk_id_categoria' => 'required|exists:categoria,id',
            'imagen'          => 'nullable|image|max:20240',
        ]);

        $imagen = null;
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create([
            'nombre'          => $request->nombre,
            'precio'          => $request->precio,
            'fk_id_categoria' => $request->fk_id_categoria,
            'imagen'          => $imagen,
        ]);

        return redirect()->route('producto.index')->with('status', '¡Producto registrado con éxito!');
    }

    public function modificarProducto(Request $request)
    {
        $request->validate([
            'id'              => 'required|exists:productos,id',
            'nombre'          => 'required|string|max:255',
            'precio'          => 'required|integer|min:0',
            'fk_id_categoria' => 'required|exists:categoria,id',
            'imagen'          => 'nullable|image|max:20240',
        ]);

        $producto = Producto::findOrFail($request->id);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $producto->imagen = $request->file('imagen')->store('productos', 'public');
        }

        $producto->nombre          = $request->nombre;
        $producto->precio          = $request->precio;
        $producto->fk_id_categoria = $request->fk_id_categoria;
        $producto->save();

        return redirect()->route('producto.index')->with('status', '¡Producto modificado con éxito!');
    }

    public function eliminarProducto($id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('producto.index')->with('status', '¡Producto eliminado con éxito!');
    }
}
