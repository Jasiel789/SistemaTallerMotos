<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'contacto', 'telefono', 'email', 'notas', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_proveedor')
            ->withPivot(['codigo_proveedor', 'precio_compra', 'fecha_precio'])
            ->withTimestamps();
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }
}
