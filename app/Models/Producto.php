<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'sku', 'nombre', 'categoria_id', 'marca', 'compatibilidad',
        'stock_actual', 'stock_minimo', 'precio_compra', 'precio_venta',
        'ubicacion', 'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedor')
            ->withPivot(['codigo_proveedor', 'precio_compra', 'fecha_precio'])
            ->withTimestamps();
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    // Ganancia por pieza (precios finales)
    public function getGananciaUnitariaAttribute(): string
    {
        return number_format($this->precio_venta - $this->precio_compra, 2, '.', '');
    }

    // Precio de venta sugerido según el margen de la categoría
    public function precioSugerido(): float
    {
        $margen = $this->categoria?->margen_porcentaje ?? 30;

        return round($this->precio_compra * (1 + $margen / 100), 2);
    }

    // Proveedor con el precio de compra más bajo
    public function proveedorMasBarato(): ?Proveedor
    {
        return $this->proveedores()->orderBy('producto_proveedor.precio_compra')->first();
    }
}
