<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\OrdenDetalle;
use App\Models\OrdenServicio;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Reglas de las órdenes de servicio.
 * Una refacción sale del stock en cuanto se agrega a la orden (la pieza ya se sacó del estante)
 * y regresa si se quita o si se cancela la orden. Al cobrar no se vuelve a descontar nada.
 */
class OrdenService
{
    public function agregarRefaccion(int $ordenId, int $productoId, int $cantidad, int $userId): void
    {
        DB::transaction(function () use ($ordenId, $productoId, $cantidad, $userId) {
            $orden = $this->ordenAbierta($ordenId);
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            if (! $producto->activo) {
                throw new RuntimeException("{$producto->nombre} está desactivado.");
            }

            if ($producto->stock_actual < $cantidad) {
                throw new RuntimeException("Stock insuficiente de {$producto->nombre} (quedan {$producto->stock_actual}).");
            }

            $detalle = $orden->detalles()->where('producto_id', $producto->id)->first();

            if ($detalle) {
                // Misma refacción otra vez: se suma, conservando el precio con el que se agregó
                $detalle->increment('cantidad', $cantidad);
            } else {
                $orden->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_venta' => $producto->precio_venta,
                    'precio_compra' => $producto->precio_compra, // para calcular la ganancia después
                ]);
            }

            $producto->decrement('stock_actual', $cantidad);

            MovimientoStock::create([
                'producto_id' => $producto->id,
                'user_id' => $userId,
                'tipo' => 'orden',
                'cantidad' => -$cantidad,
                'referencia_type' => OrdenServicio::class,
                'referencia_id' => $orden->id,
                'motivo' => "Refacción usada en la orden #{$orden->id}",
            ]);

            $this->recalcular($orden);
        });
    }

    public function quitarRefaccion(int $ordenId, int $detalleId, int $userId): void
    {
        DB::transaction(function () use ($ordenId, $detalleId, $userId) {
            $orden = $this->ordenAbierta($ordenId);
            $detalle = OrdenDetalle::where('orden_id', $orden->id)->findOrFail($detalleId);

            $this->devolverAlStock($orden, $detalle, $userId, "Refacción quitada de la orden #{$orden->id}");
            $detalle->delete();

            $this->recalcular($orden);
        });
    }

    public function actualizarDatos(int $ordenId, ?string $diagnostico, float $manoDeObra): void
    {
        DB::transaction(function () use ($ordenId, $diagnostico, $manoDeObra) {
            $orden = $this->ordenAbierta($ordenId);
            $orden->update(['diagnostico' => $diagnostico, 'mano_de_obra' => $manoDeObra]);

            $this->recalcular($orden);
        });
    }

    public function cambiarEstado(int $ordenId, string $estado): void
    {
        if (! in_array($estado, OrdenServicio::ESTADOS_ABIERTOS, true)) {
            throw new RuntimeException('Estado no válido. Para cerrar la orden usa "Cobrar y entregar" o "Cancelar".');
        }

        DB::transaction(function () use ($ordenId, $estado) {
            $this->ordenAbierta($ordenId)->update(['estado' => $estado]);
        });
    }

    /** Cobra la orden: crea la venta (refacciones + mano de obra) y la marca como entregada. */
    public function entregar(int $ordenId, int $userId, string $metodoPago): Venta
    {
        return DB::transaction(function () use ($ordenId, $userId, $metodoPago) {
            $orden = $this->ordenAbierta($ordenId);
            $this->recalcular($orden);

            $detalles = $orden->detalles()->get();

            if ($detalles->isEmpty() && (float) $orden->mano_de_obra <= 0) {
                throw new RuntimeException('La orden no tiene refacciones ni mano de obra; no hay nada que cobrar.');
            }

            $venta = Venta::create([
                'cliente_id' => $orden->cliente_id,
                'orden_id' => $orden->id,
                'user_id' => $userId,
                'fecha' => now(),
                'metodo_pago' => $metodoPago,
                'total' => $orden->total,
                'mano_de_obra' => $orden->mano_de_obra,
            ]);

            foreach ($detalles as $d) {
                $venta->detalles()->create([
                    'producto_id' => $d->producto_id,
                    'cantidad' => $d->cantidad,
                    'precio_venta' => $d->precio_venta,
                    'precio_compra' => $d->precio_compra,
                ]);
            }

            $orden->update(['estado' => 'entregada']);

            return $venta;
        });
    }

    /** Cancela la orden y regresa todas las refacciones al stock. */
    public function cancelar(int $ordenId, int $userId): void
    {
        DB::transaction(function () use ($ordenId, $userId) {
            $orden = $this->ordenAbierta($ordenId);

            foreach ($orden->detalles()->get() as $detalle) {
                $this->devolverAlStock($orden, $detalle, $userId, "Orden #{$orden->id} cancelada");
            }

            $orden->update(['estado' => 'cancelada']);
        });
    }

    private function ordenAbierta(int $id): OrdenServicio
    {
        $orden = OrdenServicio::lockForUpdate()->findOrFail($id);

        if (! $orden->estaAbierta()) {
            throw new RuntimeException('La orden ya está cerrada y no se puede modificar.');
        }

        return $orden;
    }

    private function devolverAlStock(OrdenServicio $orden, OrdenDetalle $detalle, int $userId, string $motivo): void
    {
        $producto = Producto::lockForUpdate()->findOrFail($detalle->producto_id);
        $producto->increment('stock_actual', $detalle->cantidad);

        MovimientoStock::create([
            'producto_id' => $producto->id,
            'user_id' => $userId,
            'tipo' => 'orden',
            'cantidad' => $detalle->cantidad,
            'referencia_type' => OrdenServicio::class,
            'referencia_id' => $orden->id,
            'motivo' => $motivo,
        ]);
    }

    private function recalcular(OrdenServicio $orden): void
    {
        $refacciones = $orden->detalles()->get()->sum(fn ($d) => $d->cantidad * $d->precio_venta);

        $orden->update(['total' => $refacciones + (float) $orden->mano_de_obra]);
    }
}
