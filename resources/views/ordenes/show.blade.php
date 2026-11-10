<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orden #{{ $orden->id }}</h2>
                @include('ordenes._badge', ['estado' => $orden->estado])
            </div>
            <a href="{{ route('ordenes.index') }}" class="no-imprimir text-sm text-gray-500 hover:underline">← Volver a órdenes</a>
        </div>
    </x-slot>

    @php
        $abierta = $orden->estaAbierta();
        $subtotal = $orden->subtotalRefacciones();
        $campo = 'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    @endphp

    <style>
        @media print {
            nav, header, .no-imprimir { display: none !important; }
            body { background: #fff !important; }
        }
    </style>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="no-imprimir rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="no-imprimir rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Columna principal --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Datos --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="text-gray-500">Cliente</dt>
                                <dd class="font-medium text-gray-900">
                                    <a href="{{ route('clientes.show', $orden->cliente) }}" class="hover:underline">{{ $orden->cliente->nombre }}</a>
                                </dd>
                                @if ($orden->cliente->telefono)
                                    <dd class="text-gray-500">{{ $orden->cliente->telefono }}</dd>
                                @endif
                            </div>
                            <div>
                                <dt class="text-gray-500">Moto</dt>
                                <dd class="font-medium text-gray-900">{{ $orden->moto->marca }} {{ $orden->moto->modelo }} {{ $orden->moto->anio }}</dd>
                                <dd class="text-gray-500">
                                    {{ $orden->moto->placa ?: 'Sin placa' }}
                                    @if ($orden->moto->kilometraje !== null) · {{ number_format($orden->moto->kilometraje) }} km @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Recibida</dt>
                                <dd class="font-medium text-gray-900">{{ $orden->fecha->format('d/m/Y') }}</dd>
                                <dd class="text-gray-500">por {{ $orden->user->name }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Diagnóstico y mano de obra --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Diagnóstico y mano de obra</h3>

                        @if ($abierta)
                            <form method="POST" action="{{ route('ordenes.update', $orden) }}" class="no-imprimir mt-4 space-y-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <x-input-label for="diagnostico" value="Falla reportada / diagnóstico" />
                                    <textarea id="diagnostico" name="diagnostico" rows="4" class="{{ $campo }}">{{ old('diagnostico', $orden->diagnostico) }}</textarea>
                                </div>
                                <div class="max-w-xs">
                                    <x-input-label for="mano_de_obra" value="Mano de obra" />
                                    <input id="mano_de_obra" name="mano_de_obra" type="number" min="0" step="0.01" value="{{ old('mano_de_obra', $orden->mano_de_obra) }}" class="{{ $campo }}">
                                </div>
                                <x-primary-button>Guardar</x-primary-button>
                            </form>
                        @else
                            <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $orden->diagnostico ?: 'Sin diagnóstico.' }}</p>
                            <p class="mt-3 text-sm text-gray-700">Mano de obra: <strong>${{ number_format($orden->mano_de_obra, 2) }}</strong></p>
                        @endif
                    </div>

                    {{-- Refacciones --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Refacciones usadas</h3>

                        @if ($orden->detalles->isEmpty())
                            <p class="mt-3 text-sm text-gray-500">Todavía no se ha usado ninguna refacción.</p>
                        @else
                            <div class="mt-3 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-left text-gray-500">
                                        <tr>
                                            <th class="py-2">Producto</th>
                                            <th class="py-2 text-right">Cant.</th>
                                            <th class="py-2 text-right">Precio</th>
                                            <th class="py-2 text-right">Importe</th>
                                            @if ($abierta) <th class="py-2 no-imprimir"></th> @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-900">
                                        @foreach ($orden->detalles as $d)
                                            <tr>
                                                <td class="py-2">
                                                    {{ $d->producto->nombre }}
                                                    <span class="text-xs text-gray-500">{{ $d->producto->sku }}</span>
                                                </td>
                                                <td class="py-2 text-right">{{ $d->cantidad }}</td>
                                                <td class="py-2 text-right">${{ number_format($d->precio_venta, 2) }}</td>
                                                <td class="py-2 text-right">${{ number_format($d->cantidad * $d->precio_venta, 2) }}</td>
                                                @if ($abierta)
                                                    <td class="py-2 text-right no-imprimir">
                                                        <form method="POST" action="{{ route('ordenes.refacciones.destroy', [$orden, $d]) }}"
                                                              onsubmit="return confirm('¿Quitar esta refacción? Regresa al stock.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-red-600 hover:underline">Quitar</button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($abierta)
                            <form method="POST" action="{{ route('ordenes.refacciones.store', $orden) }}"
                                  class="no-imprimir mt-4 border-t border-gray-100 pt-4 space-y-3"
                                  x-data="{
                                      productos: @js($productos),
                                      q: '',
                                      sel: null,
                                      cant: 1,
                                      res() {
                                          const t = this.q.trim().toLowerCase();
                                          if (!t) { return []; }
                                          return this.productos.filter(p =>
                                              (p.sku + ' ' + p.nombre + ' ' + (p.marca || '') + ' ' + (p.compatibilidad || '')).toLowerCase().includes(t)
                                          ).slice(0, 8);
                                      },
                                      elegir(p) { this.sel = p; this.cant = 1; this.q = ''; },
                                      fmt(n) { return '$' + Number(n).toFixed(2); }
                                  }">
                                @csrf
                                <input type="hidden" name="producto_id" :value="sel ? sel.id : ''">

                                <div>
                                    <x-input-label for="buscar_refaccion" value="Agregar refacción" />
                                    <input id="buscar_refaccion" type="text" x-model="q" autocomplete="off"
                                           placeholder="Busca por código, nombre, marca o modelo de moto…" class="{{ $campo }}">
                                    <button type="button" class="mt-1 text-xs text-indigo-600 hover:underline"
                                            @click="q = @js($orden->moto->modelo)">
                                        Buscar piezas para {{ $orden->moto->modelo }}
                                    </button>
                                </div>

                                <ul class="divide-y divide-gray-100">
                                    <template x-for="p in res()" :key="p.id">
                                        <li>
                                            <button type="button" @click="elegir(p)" :disabled="p.stock_actual < 1"
                                                    class="w-full text-left px-2 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                                                <span>
                                                    <span class="block font-medium text-gray-900" x-text="p.nombre"></span>
                                                    <span class="block text-xs text-gray-500"
                                                          x-text="p.sku + (p.marca ? ' · ' + p.marca : '') + (p.compatibilidad ? ' · ' + p.compatibilidad : '')"></span>
                                                </span>
                                                <span class="text-right shrink-0">
                                                    <span class="block font-semibold text-gray-900" x-text="fmt(p.precio_venta)"></span>
                                                    <span class="block text-xs" :class="p.stock_actual < 1 ? 'text-red-600' : 'text-gray-500'"
                                                          x-text="p.stock_actual < 1 ? 'Agotado' : p.stock_actual + ' en stock'"></span>
                                                </span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>

                                <div x-show="sel" x-cloak class="flex flex-wrap items-end gap-3 rounded-md bg-indigo-50 p-3">
                                    <div class="flex-1 min-w-[12rem] text-sm">
                                        <div class="font-medium text-gray-900" x-text="sel ? sel.nombre : ''"></div>
                                        <div class="text-xs text-gray-600"
                                             x-text="sel ? fmt(sel.precio_venta) + ' c/u · ' + sel.stock_actual + ' en stock' : ''"></div>
                                    </div>
                                    <div>
                                        <label for="cantidad" class="block text-xs text-gray-600">Cantidad</label>
                                        <input id="cantidad" name="cantidad" type="number" min="1" :max="sel ? sel.stock_actual : null" x-model.number="cant"
                                               class="mt-1 w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <x-primary-button>Agregar a la orden</x-primary-button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Columna lateral: total y acciones --}}
                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Refacciones</span><span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Mano de obra</span><span>${{ number_format($orden->mano_de_obra, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-3 text-xl font-semibold text-gray-900">
                            <span>Total</span><span>${{ number_format($orden->total, 2) }}</span>
                        </div>
                    </div>

                    @if ($abierta)
                        <div class="no-imprimir bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                            <form method="POST" action="{{ route('ordenes.estado', $orden) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <x-input-label for="estado" value="Avance de la reparación" />
                                <div class="flex gap-2">
                                    <select id="estado" name="estado" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach (\App\Models\OrdenServicio::ESTADOS_ABIERTOS as $e)
                                            <option value="{{ $e }}" @selected($orden->estado === $e)>{{ \App\Models\OrdenServicio::ETIQUETAS[$e] }}</option>
                                        @endforeach
                                    </select>
                                    <button class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Cambiar
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('ordenes.entregar', $orden) }}" class="space-y-2 border-t border-gray-100 pt-5"
                                  onsubmit="return confirm('¿Cobrar ${{ number_format($orden->total, 2) }} y marcar la orden como entregada?')">
                                @csrf
                                <x-input-label for="metodo_pago" value="Cobrar y entregar" />
                                <select id="metodo_pago" name="metodo_pago" class="{{ $campo }}">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                                <x-primary-button class="w-full justify-center">Cobrar ${{ number_format($orden->total, 2) }}</x-primary-button>
                            </form>

                            <form method="POST" action="{{ route('ordenes.cancelar', $orden) }}" class="border-t border-gray-100 pt-5"
                                  onsubmit="return confirm('¿Cancelar la orden? Las refacciones regresan al stock.')">
                                @csrf
                                <button class="text-sm text-red-600 hover:underline">Cancelar orden</button>
                            </form>
                        </div>
                    @elseif ($orden->estado === 'entregada' && $orden->venta)
                        <div class="no-imprimir bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-3">
                            <p class="text-gray-700">
                                Cobrada el {{ $orden->venta->fecha->format('d/m/Y H:i') }}
                                con <span class="capitalize">{{ $orden->venta->metodo_pago }}</span>.
                            </p>
                            <a href="{{ route('ventas.show', $orden->venta) }}" class="text-indigo-600 hover:underline">Ver la venta #{{ $orden->venta->id }}</a>
                        </div>
                    @endif

                    <div class="no-imprimir">
                        <button type="button" onclick="window.print()" class="text-sm text-gray-500 hover:underline">Imprimir orden</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
