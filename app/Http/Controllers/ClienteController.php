<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::withCount(['motos', 'ordenes'])
            ->when($request->q, function ($query, $texto) {
                $query->where(function ($w) use ($texto) {
                    $w->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('telefono', 'like', "%{$texto}%")
                        ->orWhere('email', 'like', "%{$texto}%")
                        ->orWhereHas('motos', fn ($m) => $m->where('placa', 'like', "%{$texto}%"));
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($request->validate($this->reglas()));

        return redirect()->route('clientes.show', $cliente)
            ->with('status', 'Cliente creado. Ahora agrega su moto.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('motos');

        $ordenes = $cliente->ordenes()->with('moto')->latest('id')->limit(10)->get();

        return view('clientes.show', compact('cliente', 'ordenes'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->validate($this->reglas()));

        return redirect()->route('clientes.show', $cliente)->with('status', 'Cliente actualizado.');
    }

    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
