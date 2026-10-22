<?php

namespace App\Http\Controllers;

use App\Models\Producto;

class CatalogoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')
            ->orderBy('fk_id_categoria')
            ->orderBy('nombre')
            ->get();

        return view('catalogo', compact('productos'));
    }
}