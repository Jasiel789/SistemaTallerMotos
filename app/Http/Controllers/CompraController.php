<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with(['proveedor', 'user'])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(15);

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        return view('compras.create', [
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'productos' => Producto::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'sku', 'nombre', 'precio_compra', 'stock_actual']),
        ]);
    }

    public function store(Request $request, InventarioService $inventario)
    {
        $data = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'folio' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id', 'distinct'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_compra' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Agrega al menos un producto.',
            'items.*.producto_id.required' => 'Elige un producto en cada renglón.',
            'items.*.producto_id.distinct' => 'Hay un producto repetido; junta las piezas en un solo renglón.',
            'items.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        ]);

        $compra = $inventario->registrarCompra(
            (int) $data['proveedor_id'],
            $request->user()->id,
            array_values($data['items']),
            $data['folio'] ?? null,
        );

        return redirect()->route('compras.show', $compra)
            ->with('status', 'Compra registrada: el stock ya se actualizó.');
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'user', 'detalles.producto']);

        return view('compras.show', compact('compra'));
    }
}
