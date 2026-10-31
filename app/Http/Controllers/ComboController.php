<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComboController extends Controller
{

    // EL combo se registrar como otro producto y no como un registro independiente

    public function mostrarCombos()
    {
        $combos = Combo::orderBy('combo')->get();

        return view('partials.combos', compact('combos'));
    }

    public function registrarCombo(Request $request)
    {
        $datos = $request->validate([
            'combo' => 'required|string|max:50|unique:combos,combo',
            '_combo_form' => 'required|in:crear',
        ]);

        Combo::create(['combo' => $datos['combo']]);

        return redirect()->route('mesas.index')->with('status', '¡Combo registrado con éxito!');
    }

    public function modificarCombo(Request $request)
    {
        $request->validate([
            'id_combo' => 'required|exists:combos,id',
            '_combo_form' => 'required|in:modificar',
        ]);

        $datos = $request->validate([
            'combo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('combos', 'combo')->ignore($request->id_combo, 'id'),
            ],
        ]);

        $combo = Combo::findOrFail($request->id_combo);
        $combo->update(['combo' => $datos['combo']]);

        return redirect()->route('mesas.index')->with('status', '¡Combo modificado con éxito!');
    }

    public function eliminarCombo($id)
    {
        $combo = Combo::findOrFail($id);

        if (DetallePedido::where('fk_id_combo', $combo->id)->exists()) {
            return redirect()->route('mesas.index')->with('error', 'No se puede eliminar un combo que ya está incluido en pedidos.');
        }

        $combo->delete();

        return redirect()->route('mesas.index')->with('status', '¡Combo eliminado con éxito!');
    }
}
