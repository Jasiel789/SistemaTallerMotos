<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva venta
        </h2>
    </x-slot>

    @php
        $campo = 'block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';

        // Si hubo un error (por ejemplo, stock insuficiente) se reconstruye el carrito
        $carritoInicial = collect(old('items', []))
            ->map(function ($it) use ($productos) {
                $p = $productos->firstWhere('id', (int) ($it['producto_id'] ?? 0));

                return $p ? [
                    'producto_id' => $p->id,
                    'sku' => $p->sku,
                    'nombre' => $p->nombre,
                    'precio' => (float) $p->precio_venta,
                    'stock' => $p->stock_actual,
                    'cantidad' => (int) ($it['cantidad'] ?? 1),
                ] : null;
            })
            ->filter()
            ->values();
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ventas.store') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-5"
                  x-data="{
                      productos: @js($productos),
                      items: @js($carritoInicial),
                      q: '',
                      metodo: @js(old('metodo_pago', 'efectivo')),
                      recibido: '',
                      fmt(n) { return '$' + Number(n).toFixed(2); },
                      resultados() {
                          const t = this.q.trim().toLowerCase();
                          if (!t) { return []; }
                          return this.productos.filter(p =>
                              (p.sku + ' ' + p.nombre + ' ' + (p.marca || '') + ' ' + (p.compatibilidad || '')).toLowerCase().includes(t)
                          ).slice(0, 8);
                      },
                      agregar(p) {
                          if (p.stock_actual < 1) { return; }
                          const ex = this.items.find(i => i.producto_id == p.id);
                          if (ex) {
                              if (ex.cantidad < ex.stock) { ex.cantidad++; }
                          } else {
                              this.items.push({
                                  producto_id: p.id, sku: p.sku, nombre: p.nombre,
                                  precio: Number(p.precio_venta), stock: p.stock_actual, cantidad: 1
                              });
                          }
                          this.q = '';
                          this.$refs.buscador.focus();
                      },
                      enter() {
                          const t = this.q.trim().toLowerCase();
                          const exacto = this.productos.find(p => p.sku.toLowerCase() === t);
                          if (exacto) { this.agregar(exacto); return; }
                          const r = this.resultados();
                          if (r.length === 1) { this.agregar(r[0]); }
                      },
                      ajustar(it) {
                          let c = parseInt(it.cantidad) || 1;
                          if (c < 1) { c = 1; }
                          if (c > it.stock) { c = it.stock; }
                          it.cantidad = c;
                      },
                      quitar(i) { this.items.splice(i, 1); },
                      total() { return this.items.reduce((s, it) => s + it.cantidad * it.precio, 0); }
                  }">
                @csrf

                {{-- Buscador --}}
                <div class="lg:col-span-3 bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div>
                        <x-input-label for="buscador" value="Buscar producto" />
                        <input id="buscador" x-ref="buscador" type="text" x-model="q" autofocus autocomplete="off"
                               @keydown.enter.prevent="enter()"
                               placeholder="Escribe o escanea código, nombre, marca o modelo de moto…"
                               class="mt-1 {{ $campo }}">
                        <p class="mt-1 text-xs text-gray-500">Enter agrega el producto si solo hay una coincidencia (o el código exacto).</p>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        <template x-for="p in resultados()" :key="p.id">
                            <li>
                                <button type="button" @click="agregar(p)" :disabled="p.stock_actual < 1"
                                        class="w-full text-left px-2 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                                    <span>
                                        <span class="block font-medium text-gray-900" x-text="p.nombre"></span>
                                        <span class="block text-xs text-gray-500"
                                              x-text="p.sku + (p.marca ? ' · ' + p.marca : '') + (p.compatibilidad ? ' · ' + p.compatibilidad : '')"></span>
                                    </span>
                                    <span class="text-right shrink-0">
                                        <span class="block font-semibold text-gray-900" x-text="fmt(p.precio_venta)"></span>
                                        <span class="block text-xs"
                                              :class="p.stock_actual < 1 ? 'text-red-600' : 'text-gray-500'"
                                              x-text="p.stock_actual < 1 ? 'Agotado' : p.stock_actual + ' en stock'"></span>
                                    </span>
                                </button>
                            </li>
                        </template>
                    </ul>

                    <p x-show="q.trim() !== '' && resultados().length === 0" class="text-sm text-gray-500">
                        No se encontró ningún producto con ese texto.
                    </p>
                </div>

                {{-- Carrito --}}
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-6 space-y-4 self-start">
                    <h3 class="text-lg font-medium text-gray-900">Carrito</h3>

                    <p x-show="items.length === 0" class="text-sm text-gray-500">Aún no has agregado productos.</p>

                    <div class="space-y-3">
                        <template x-for="(it, i) in items" :key="it.producto_id">
                            <div class="rounded-md border border-gray-200 p-3">
                                <input type="hidden" :name="'items[' + i + '][producto_id]'" :value="it.producto_id">

                                <div class="flex items-start justify-between gap-2">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900" x-text="it.nombre"></div>
                                        <div class="text-xs text-gray-500" x-text="it.sku + ' · ' + fmt(it.precio) + ' c/u'"></div>
                                    </div>
                                    <button type="button" @click="quitar(i)" class="text-red-600 hover:text-red-800" title="Quitar">✕</button>
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="it.cantidad--; ajustar(it)"
                                                class="h-8 w-8 rounded border border-gray-300 text-gray-700">−</button>
                                        <input type="number" min="1" :max="it.stock" :name="'items[' + i + '][cantidad]'"
                                               x-model.number="it.cantidad" @change="ajustar(it)"
                                               class="h-8 w-16 text-center border-gray-300 rounded-md shadow-sm text-sm">
                                        <button type="button" @click="it.cantidad++; ajustar(it)"
                                                class="h-8 w-8 rounded border border-gray-300 text-gray-700">+</button>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900" x-text="fmt(it.cantidad * it.precio)"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-4">
                        <div class="flex items-center justify-between text-xl font-semibold text-gray-900">
                            <span>Total</span>
                            <span x-text="fmt(total())"></span>
                        </div>

                        <div>
                            <x-input-label for="metodo_pago" value="Método de pago" />
                            <select id="metodo_pago" name="metodo_pago" x-model="metodo" class="mt-1 {{ $campo }}">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div x-show="metodo === 'efectivo'" class="space-y-1">
                            <x-input-label for="recibido" value="Efectivo recibido (solo para calcular el cambio)" />
                            <input id="recibido" type="number" min="0" step="0.01" x-model.number="recibido" class="mt-1 {{ $campo }}">
                            <p x-show="recibido > 0" class="text-sm font-medium"
                               :class="recibido >= total() ? 'text-green-700' : 'text-red-600'"
                               x-text="recibido >= total() ? 'Cambio: ' + fmt(recibido - total()) : 'Faltan ' + fmt(total() - recibido)"></p>
                        </div>

                        <x-primary-button class="w-full justify-center" x-bind:disabled="items.length === 0">
                            Cobrar y registrar venta
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
