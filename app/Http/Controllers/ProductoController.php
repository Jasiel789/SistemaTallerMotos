<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->when($request->q, function ($query, $texto) {
                $query->where(function ($w) use ($texto) {
                    $w->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('sku', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%")
                        ->orWhere('compatibilidad', 'like', "%{$texto}%");
                });
            })
            ->when($request->boolean('bajo'), fn ($query) => $query->stockBajo())
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create', [
            'producto' => new Producto(['stock_minimo' => 2]),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'marca' => ['nullable', 'string', 'max:100'],
            'compatibilidad' => ['nullable', 'string', 'max:1000'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
        ]);

        $data['stock_actual'] = $data['stock_actual'] ?? 0;
        $data['activo'] = true;

        DB::transaction(function () use ($data, $request) {
            $producto = Producto::create($data);

            // El stock inicial queda registrado en la bitácora
            if ($producto->stock_actual > 0) {
                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'ajuste',
                    'cantidad' => $producto->stock_actual,
                    'motivo' => 'Inventario inicial',
                ]);
            }
        });

        return redirect()->route('productos.index')->with('status', 'Producto creado.');
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('productos', 'sku')->ignore($producto->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'marca' => ['nullable', 'string', 'max:100'],
            'compatibilidad' => ['nullable', 'string', 'max:1000'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
        ]);

        // El stock NO se edita aquí: solo cambia con compras, ventas o ajustes
        $data['activo'] = $request->boolean('activo');

        $producto->update($data);

        return redirect()->route('productos.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        // Si ya tiene historial, se desactiva en lugar de borrarse
        if ($producto->movimientos()->exists()) {
            $producto->update(['activo' => false]);

            return redirect()->route('productos.index')
                ->with('status', 'El producto tiene historial, así que se desactivó en lugar de borrarse.');
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado.');
    }
}
