<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Moto;
use Illuminate\Http\Request;

class MotoController extends Controller
{
    public function store(Request $request, Cliente $cliente)
    {
        $cliente->motos()->create($request->validate($this->reglas()));

        return redirect()->route('clientes.show', $cliente)->with('status', 'Moto agregada.');
    }

    public function edit(Moto $moto)
    {
        $moto->load('cliente');

        return view('motos.edit', compact('moto'));
    }

    public function update(Request $request, Moto $moto)
    {
        $moto->update($request->validate($this->reglas()));

        return redirect()->route('clientes.show', $moto->cliente_id)->with('status', 'Moto actualizada.');
    }

    public function destroy(Moto $moto)
    {
        if ($moto->ordenes()->exists()) {
            return back()->withErrors(['moto' => 'Esa moto ya tiene órdenes de servicio, por eso no se puede borrar.']);
        }

        $clienteId = $moto->cliente_id;
        $moto->delete();

        return redirect()->route('clientes.show', $clienteId)->with('status', 'Moto eliminada.');
    }

    private function reglas(): array
    {
        return [
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'anio' => ['nullable', 'integer', 'between:1950,'.(now()->year + 1)],
            'placa' => ['nullable', 'string', 'max:20'],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
