<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;

class CatalogoController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('categoria')->get();

        $productos = Producto::with('categoria')
            ->whereHas('categoria')
            ->orderBy('fk_id_categoria')
            ->orderBy('nombre')
            ->get();

        return view('catalogo', compact('productos', 'categorias'));
    }
}
