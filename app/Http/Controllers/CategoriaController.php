<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;


class CategoriaController extends Controller
{
    public function registroCategoria(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string|max:50'
        ]);

        Categoria::create([
            'categoria' => $request->categoria
        ]);

        return redirect()->route('producto.index')->with('status', '¡Categoría registrada con éxito!');
    }

    public function mostrarCategoria(Request $request)
    {
        $categorias = Categoria::orderBy('categoria')->get();

        return view('partials.categorias', compact('categorias'));
    }

    public function modificarCategoria(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:categoria,id',
            'categoria' => 'required|string|max:50',
        ]);

        $categoria = Categoria::findOrFail($request->id);
        $categoria->update(['categoria' => $request->categoria]);

        return redirect()->route('producto.index')->with('status', '¡Categoría modificada con éxito!');
    }

    public function eliminarCategoria($id)
    {
        $categoria = Categoria::findOrFail($id);

        if (Producto::where('fk_id_categoria', $categoria->id)->exists()) {
            return redirect()->route('producto.index')->with('error', 'No se puede eliminar una categoría que tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('producto.index')->with('status', '¡Categoría eliminada con éxito!');
    }
}
