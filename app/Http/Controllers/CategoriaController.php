<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;


class CategoriaController extends Controller
{
    public function registroCategoria(Request $request){

        $request->validate([
            'categoria' => 'required|string'
        ]);

        Categoria::create([
            'categoria' => $request->categoria
        ]);

        return redirect()->route('producto.index')->with('status', '¡Categoría registrada con éxito!');
    }
}
