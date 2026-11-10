@php
    $editando = $producto->exists;
    $campo = 'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2"
     x-data="{
        compra: Number(@js(old('precio_compra', $producto->precio_compra ?? 0))),
        venta: Number(@js(old('precio_venta', $producto->precio_venta ?? 0)))
     }">

    <div>
        <x-input-label for="sku" value="Código (SKU)" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $producto->sku)" required />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $producto->nombre)" required />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="categoria_id" value="Categoría" />
        <select id="categoria_id" name="categoria_id" class="{{ $campo }}">
            <option value="">Sin categoría</option>
            @foreach ($categorias as $c)
                <option value="{{ $c->id }}" @selected(old('categoria_id', $producto->categoria_id) == $c->id)>{{ $c->nombre }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="marca" value="Marca" />
        <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full" :value="old('marca', $producto->marca)" />
        <x-input-error :messages="$errors->get('marca')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="compatibilidad" value="Compatible con (modelos de moto)" />
        <textarea id="compatibilidad" name="compatibilidad" rows="2" class="{{ $campo }}"
                  placeholder="Ej. Italika FT150, Honda CG125, Yamaha FZ16">{{ old('compatibilidad', $producto->compatibilidad) }}</textarea>
        <x-input-error :messages="$errors->get('compatibilidad')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="precio_compra" value="Precio de compra (lo que te cuesta)" />
        <x-text-input id="precio_compra" name="precio_compra" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      x-model.number="compra" :value="old('precio_compra', $producto->precio_compra)" required />
        <x-input-error :messages="$errors->get('precio_compra')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="precio_venta" value="Precio de venta (precio final al cliente)" />
        <x-text-input id="precio_venta" name="precio_venta" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      x-model.number="venta" :value="old('precio_venta', $producto->precio_venta)" required />
        <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
    </div>

    <div class="sm:col-span-2 rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-700">
        Ganancia por pieza:
        <span class="font-semibold" x-text="'$' + (venta - compra).toFixed(2)"></span>
        <span class="text-gray-500"
              x-text="compra > 0 ? '(' + (((venta - compra) / compra) * 100).toFixed(0) + '% sobre el costo)' : ''"></span>
    </div>

    @unless ($editando)
        <div>
            <x-input-label for="stock_actual" value="Stock inicial (piezas que hay hoy)" />
            <x-text-input id="stock_actual" name="stock_actual" type="number" min="0" class="mt-1 block w-full" :value="old('stock_actual', 0)" />
            <x-input-error :messages="$errors->get('stock_actual')" class="mt-2" />
        </div>
    @endunless

    <div>
        <x-input-label for="stock_minimo" value="Stock mínimo (aviso para resurtir)" />
        <x-text-input id="stock_minimo" name="stock_minimo" type="number" min="0" class="mt-1 block w-full" :value="old('stock_minimo', $producto->stock_minimo)" required />
        <x-input-error :messages="$errors->get('stock_minimo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ubicacion" value="Ubicación en el taller" />
        <x-text-input id="ubicacion" name="ubicacion" type="text" class="mt-1 block w-full" :value="old('ubicacion', $producto->ubicacion)" placeholder="Ej. Estante B, nivel 2" />
        <x-input-error :messages="$errors->get('ubicacion')" class="mt-2" />
    </div>

    @if ($editando)
        <div class="sm:col-span-2 space-y-1">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $producto->activo)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                Producto activo
            </label>
            <p class="text-xs text-gray-500">
                Stock actual: <strong>{{ $producto->stock_actual }}</strong>. El stock cambia con compras, ventas o ajustes, no desde aquí.
            </p>
        </div>
    @endif
</div>
