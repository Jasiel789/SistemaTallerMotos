<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use RuntimeException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['fecha' => ['nullable', 'date']]);

        $fecha = $request->input('fecha', now()->toDateString());

        $base = Venta::whereDate('fecha', $fecha);

        $totalDia = (clone $base)->sum('total');
        $cantidadDia = (clone $base)->count();

        $ventas = $base->with('user')
            ->withCount('detalles')
            ->latest('fecha')
            ->paginate(20)
            ->withQueryString();

        return view('ventas.index', compact('ventas', 'fecha', 'totalDia', 'cantidadDia'));
    }

    public function create()
    {
        return view('ventas.create', [
            'productos' => Producto::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'sku', 'nombre', 'marca', 'compatibilidad', 'precio_venta', 'stock_actual']),
        ]);
    }

    public function store(Request $request, InventarioService $inventario)
    {
        $data = $request->validate([
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,transferencia'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id', 'distinct'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Agrega al menos un producto al carrito.',
            'items.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        ]);

        try {
            $venta = $inventario->registrarVenta(
                $request->user()->id,
                array_values($data['items']),
                $data['metodo_pago'],
            );
        } catch (RuntimeException $e) {
            // Por ejemplo: stock insuficiente. No se guarda nada.
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('ventas.show', $venta)->with('status', 'Venta registrada.');
    }

    public function show(Venta $venta)
    {
        $venta->load(['user', 'detalles.producto']);

        return view('ventas.show', compact('venta'));
    }
}
