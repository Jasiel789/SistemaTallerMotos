<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva compra a proveedor
        </h2>
    </x-slot>

    @php
        $filasIniciales = array_values(old('items', [['producto_id' => '', 'cantidad' => 1, 'precio_compra' => 0]]));
        $campo = 'block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if ($proveedores->isEmpty() || $productos->isEmpty())
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800 mb-4">
                    Para registrar una compra necesitas tener al menos
                    <a href="{{ route('proveedores.create') }}" class="underline">un proveedor</a> y
                    <a href="{{ route('productos.create') }}" class="underline">un producto</a>.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800 mb-4">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('compras.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6"
                  x-data="{
                      productos: @js($productos),
                      rows: @js($filasIniciales),
                      agregar() { this.rows.push({ producto_id: '', cantidad: 1, precio_compra: 0 }); },
                      quitar(i) { this.rows.splice(i, 1); if (this.rows.length === 0) { this.agregar(); } },
                      llenar(row) {
                          const p = this.productos.find(p => p.id == row.producto_id);
                          if (p) { row.precio_compra = Number(p.precio_compra); }
                      },
                      stock(row) {
                          const p = this.productos.find(p => p.id == row.producto_id);
                          return p ? p.stock_actual : '';
                      },
                      total() {
                          return this.rows.reduce((s, r) => s + (Number(r.cantidad) || 0) * (Number(r.precio_compra) || 0), 0);
                      }
                  }">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="proveedor_id" value="Proveedor" />
                        <select id="proveedor_id" name="proveedor_id" required class="mt-1 {{ $campo }}">
                            <option value="">Elige un proveedor…</option>
                            @foreach ($proveedores as $pr)
                                <option value="{{ $pr->id }}" @selected(old('proveedor_id') == $pr->id)>{{ $pr->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="folio" value="Folio o nota del proveedor (opcional)" />
                        <x-text-input id="folio" name="folio" type="text" class="mt-1 block w-full" :value="old('folio')" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-2">Producto</th>
                                <th class="py-2 px-2 w-20">En stock</th>
                                <th class="py-2 px-2 w-28">Cantidad</th>
                                <th class="py-2 px-2 w-36">Precio de compra</th>
                                <th class="py-2 px-2 w-28 text-right">Subtotal</th>
                                <th class="py-2 pl-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="i">
                                <tr class="align-top">
                                    <td class="py-1 pr-2">
                                        <select :name="'items[' + i + '][producto_id]'" x-model="row.producto_id" @change="llenar(row)"
                                                class="{{ $campo }}">
                                            <option value="">Elige un producto…</option>
                                            @foreach ($productos as $p)
                                                <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-1 px-2 pt-3 text-gray-500" x-text="stock(row)"></td>
                                    <td class="py-1 px-2">
                                        <input type="number" min="1" step="1" :name="'items[' + i + '][cantidad]'" x-model.number="row.cantidad"
                                               class="{{ $campo }}">
                                    </td>
                                    <td class="py-1 px-2">
                                        <input type="number" min="0" step="0.01" :name="'items[' + i + '][precio_compra]'" x-model.number="row.precio_compra"
                                               class="{{ $campo }}">
                                    </td>
                                    <td class="py-1 px-2 pt-3 text-right text-gray-900"
                                        x-text="'$' + ((Number(row.cantidad) || 0) * (Number(row.precio_compra) || 0)).toFixed(2)"></td>
                                    <td class="py-1 pl-2 pt-2">
                                        <button type="button" @click="quitar(i)" class="text-red-600 hover:text-red-800" title="Quitar renglón">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4 text-sm">
                        <button type="button" @click="agregar()" class="text-indigo-600 hover:underline">+ Agregar otro producto</button>
                        <a href="{{ route('productos.create') }}" target="_blank" class="text-gray-500 hover:underline">¿No está? Crear producto (se abre en otra pestaña)</a>
                    </div>
                    <div class="text-lg font-semibold text-gray-900">
                        Total: <span x-text="'$' + total().toFixed(2)"></span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Registrar compra</x-primary-button>
                    <a href="{{ route('compras.index') }}" class="text-sm text-gray-500 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
