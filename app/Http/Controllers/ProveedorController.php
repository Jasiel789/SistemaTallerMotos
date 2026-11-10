<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::withCount(['productos', 'compras'])
            ->when($request->q, fn ($query, $texto) => $query->where('nombre', 'like', "%{$texto}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create', ['proveedor' => new Proveedor()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->reglas());
        $data['activo'] = true;

        Proveedor::create($data);

        return redirect()->route('proveedores.index')->with('status', 'Proveedor creado.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate($this->reglas());
        $data['activo'] = $request->boolean('activo');

        $proveedor->update($data);

        return redirect()->route('proveedores.index')->with('status', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        // Si ya hay compras con este proveedor, se desactiva para no perder el historial
        if ($proveedor->compras()->exists()) {
            $proveedor->update(['activo' => false]);

            return redirect()->route('proveedores.index')
                ->with('status', 'El proveedor tiene compras registradas, así que se desactivó en lugar de borrarse.');
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('status', 'Proveedor eliminado.');
    }

    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
