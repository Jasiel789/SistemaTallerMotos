<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDetalle extends Model
{
    protected $table = 'orden_detalles';

    protected $fillable = ['orden_id', 'producto_id', 'cantidad', 'precio_venta', 'precio_compra'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
