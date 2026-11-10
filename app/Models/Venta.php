<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = ['cliente_id', 'orden_id', 'user_id', 'fecha', 'metodo_pago', 'total', 'mano_de_obra'];

    protected $casts = ['fecha' => 'datetime', 'total' => 'decimal:2', 'mano_de_obra' => 'decimal:2'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    // Ganancia = refacciones (venta - costo) + mano de obra
    public function getGananciaAttribute(): float
    {
        $refacciones = $this->detalles->sum(fn ($d) => ($d->precio_venta - $d->precio_compra) * $d->cantidad);

        return $refacciones + (float) $this->mano_de_obra;
    }
}
