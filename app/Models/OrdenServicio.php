<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdenServicio extends Model
{
    public const ESTADOS_ABIERTOS = ['recibida', 'en_proceso', 'lista'];

    public const ETIQUETAS = [
        'recibida' => 'Recibida',
        'en_proceso' => 'En proceso',
        'lista' => 'Lista para entregar',
        'entregada' => 'Entregada',
        'cancelada' => 'Cancelada',
    ];

    protected $table = 'ordenes_servicio';

    protected $fillable = ['cliente_id', 'moto_id', 'user_id', 'fecha', 'estado', 'diagnostico', 'mano_de_obra', 'total'];

    protected $casts = ['fecha' => 'date', 'mano_de_obra' => 'decimal:2', 'total' => 'decimal:2'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_id');
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class, 'orden_id');
    }

    public function estaAbierta(): bool
    {
        return in_array($this->estado, self::ESTADOS_ABIERTOS, true);
    }

    public function etiquetaEstado(): string
    {
        return self::ETIQUETAS[$this->estado] ?? $this->estado;
    }

    public function subtotalRefacciones(): float
    {
        return (float) $this->detalles->sum(fn ($d) => $d->cantidad * $d->precio_venta);
    }
}
