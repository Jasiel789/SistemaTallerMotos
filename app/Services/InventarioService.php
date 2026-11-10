<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Toda entrada/salida de stock pasa por aquí, dentro de una transacción.
 */
class InventarioService
{
    /**
     * @param array $items [['producto_id' => 1, 'cantidad' => 2, 'precio_compra' => 150.00], ...]
     */
    public function registrarCompra(int $proveedorId, int $userId, array $items, ?string $folio = null): Compra
    {
        return DB::transaction(function () use ($proveedorId, $userId, $items, $folio) {
            $compra = Compra::create([
                'proveedor_id' => $proveedorId,
                'user_id' => $userId,
                'fecha' => now()->toDateString(),
                'folio' => $folio,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);

                $compra->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_compra' => $item['precio_compra'],
                ]);

                $producto->increment('stock_actual', $item['cantidad']);
                $producto->update(['precio_compra' => $item['precio_compra']]);

                // Guarda el precio de este proveedor para poder compararlos
                $producto->proveedores()->syncWithoutDetaching([
                    $proveedorId => [
                        'precio_compra' => $item['precio_compra'],
                        'fecha_precio' => now()->toDateString(),
                    ],
                ]);

                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'user_id' => $userId,
                    'tipo' => 'compra',
                    'cantidad' => $item['cantidad'],
                    'referencia_type' => Compra::class,
                    'referencia_id' => $compra->id,
                ]);

                $total += $item['cantidad'] * $item['precio_compra'];
            }

            $compra->update(['total' => $total]);

            return $compra;
        });
    }

    /**
     * @param array $items [['producto_id' => 1, 'cantidad' => 2], ...] — el precio sale del producto
     */
    public function registrarVenta(int $userId, array $items, string $metodoPago = 'efectivo', ?int $clienteId = null): Venta
    {
        return DB::transaction(function () use ($userId, $items, $metodoPago, $clienteId) {
            $venta = Venta::create([
                'cliente_id' => $clienteId,
                'user_id' => $userId,
                'fecha' => now(),
                'metodo_pago' => $metodoPago,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);

                if ($producto->stock_actual < $item['cantidad']) {
                    throw new RuntimeException("Stock insuficiente de {$producto->nombre} (quedan {$producto->stock_actual}).");
                }

                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_venta' => $producto->precio_venta,
                    'precio_compra' => $producto->precio_compra,
                ]);

                $producto->decrement('stock_actual', $item['cantidad']);

                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'user_id' => $userId,
                    'tipo' => 'venta',
                    'cantidad' => -$item['cantidad'],
                    'referencia_type' => Venta::class,
                    'referencia_id' => $venta->id,
                ]);

                $total += $item['cantidad'] * $producto->precio_venta;
            }

            $venta->update(['total' => $total]);

            return $venta;
        });
    }
}
