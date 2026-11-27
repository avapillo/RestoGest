<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pendiente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PendientesController extends Controller
{
    // Mostrar la pantalla de pendientes
    public function index()
    {
        $pendientes = Pendiente::with('cliente')
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id')
            ->get();

        return view('interfaz_pendientes', compact('pendientes'));
    }

    // Registrar un pendiente
    public function store(Request $request)
    {
        $nombre = trim((string) $request->input('nombre', ''));
        $request->merge(['nombre' => $nombre]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'regex:/^\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*$/u'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'dni_ultimos_4' => ['nullable', 'regex:/^\d{4}$/'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'dni_ultimos_4.regex' => 'Ingresá exactamente 4 dígitos numéricos.',
        ]);

        DB::transaction(function () use ($datos, $nombre): void {
            if (! empty($datos['cliente_id'])) {
                $cliente = Cliente::findOrFail($datos['cliente_id']);

                if ($cliente->nombre !== $nombre) {
                    throw ValidationException::withMessages([
                        'nombre' => 'El cliente seleccionado no coincide con el nombre ingresado. Volvé a buscarlo.',
                    ]);
                }
            } else {
                $coincidencias = Cliente::where('nombre', $nombre)
                    ->get()
                    ->filter(fn (Cliente $cliente) => $cliente->nombre === $nombre);

                if ($coincidencias->isEmpty()) {
                    $cliente = Cliente::create([
                        'nombre' => $nombre,
                        'dni_ultimos_4' => null,
                    ]);
                } else {
                    $dni = $datos['dni_ultimos_4'] ?? null;

                    if ($dni === null) {
                        throw ValidationException::withMessages([
                            'nombre' => 'Ya hay clientes con ese nombre. Buscalos y elegí uno, o indicá los últimos 4 dígitos para crear un homónimo.',
                        ]);
                    }

                    if ($coincidencias->contains('dni_ultimos_4', $dni)) {
                        throw ValidationException::withMessages([
                            'dni_ultimos_4' => 'Ya existe un cliente con ese nombre y esos últimos 4 dígitos. Buscalo y elegilo.',
                        ]);
                    }

                    $cliente = Cliente::create([
                        'nombre' => $nombre,
                        'dni_ultimos_4' => $dni,
                    ]);
                }
            }

            Pendiente::create([
                'nombre' => $cliente->nombre,
                'cliente_id' => $cliente->id,
                'monto' => $datos['monto'],
                'fecha_registro' => now(),
            ]);
        });

        return redirect()->route('pendientes.index')
            ->with('status', 'Registro agregado correctamente.');
    }

    // Modificar un pendiente
    public function update(Request $request, $id)
    {
        if ($request->has('nombre')) {
            $request->merge([
                'nombre' => trim((string) $request->input('nombre')),
            ]);
        }

        $datos = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:150', 'regex:/^\p{L}[\p{L}\p{M}]*(?: \p{L}[\p{L}\p{M}]*)*$/u'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
        ]);

        $pendiente = Pendiente::with('cliente')->findOrFail($id);

        if (isset($datos['nombre']) && $datos['nombre'] !== $pendiente->cliente->nombre) {
            throw ValidationException::withMessages([
                'nombre' => 'El nombre identifica al cliente y no se puede cambiar al editar un movimiento.',
            ]);
        }

        $pendiente->update(['monto' => $datos['monto']]);

        return redirect()->route('pendientes.index')
            ->with('status', 'Registro modificado correctamente.');
    }

    // Eliminar un pendiente
    public function destroy($id)
    {
        $pendiente = Pendiente::findOrFail($id);
        $pendiente->delete();

        return redirect()->route('pendientes.index')
            ->with('status', 'Registro eliminado correctamente.');
    }

    public function buscarPersona(Request $request)
    {
        if ($request->filled('nombre')) {
            $nombre = trim((string) $request->query('nombre'));
            validator(['nombre' => $nombre], [
                'nombre' => ['required', 'string', 'max:150'],
            ])->validate();

            $clientes = Cliente::where('nombre', $nombre)
                ->get()
                ->filter(fn (Cliente $cliente) => $cliente->nombre === $nombre)
                ->values();

            $clientes->load(['pendientes' => fn ($query) => $query
                ->orderByDesc('fecha_registro')
                ->orderByDesc('id')]);

            return response()->json([
                'coincidencias' => $clientes->map(function (Cliente $cliente): array {
                    $ultimoRegistro = $cliente->pendientes->first();

                    return [
                        'id' => $cliente->id,
                        'nombre' => $cliente->nombre,
                        'dni_ultimos_4' => $cliente->dni_ultimos_4,
                        'ultimo_registro' => $ultimoRegistro ? [
                            'monto' => $ultimoRegistro->monto,
                            'fecha_registro' => $ultimoRegistro->fecha_registro?->format('d/m/Y H:i'),
                        ] : null,
                    ];
                }),
            ]);
        }

        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $busqueda = trim((string) ($datos['q'] ?? ''));

        if ($busqueda === '') {
            return response()->json(['resultados' => []]);
        }

        $registros = Pendiente::with('cliente')
            ->whereHas('cliente', function ($query) use ($busqueda): void {
                if (preg_match('/^\d{4}$/', $busqueda)) {
                    $query->where('dni_ultimos_4', $busqueda);
                } else {
                    $query->where('nombre', 'like', '%'.$busqueda.'%');
                }
            })
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'resultados' => $registros->map(fn (Pendiente $registro): array => [
                'id' => $registro->id,
                'cliente_id' => $registro->cliente_id,
                'nombre' => $registro->cliente->nombre,
                'dni_ultimos_4' => $registro->cliente->dni_ultimos_4,
                'monto' => $registro->monto,
                'fecha_registro' => $registro->fecha_registro?->format('d/m/Y H:i'),
            ]),
        ]);
    }
}
