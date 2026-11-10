<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Compra #{{ $compra->id }}
            </h2>
            <a href="{{ route('compras.index') }}" class="text-sm text-gray-500 hover:underline">← Volver a compras</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-gray-500">Proveedor</dt>
                        <dd class="font-medium text-gray-900">{{ $compra->proveedor->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Fecha</dt>
                        <dd class="font-medium text-gray-900">{{ $compra->fecha->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Folio</dt>
                        <dd class="font-medium text-gray-900">{{ $compra->folio ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Registró</dt>
                        <dd class="font-medium text-gray-900">{{ $compra->user->name }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3 text-right">Cantidad</th>
                            <th class="px-4 py-3 text-right">Precio de compra</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @foreach ($compra->detalles as $d)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('productos.edit', $d->producto) }}" class="hover:underline">{{ $d->producto->nombre }}</a>
                                    <span class="text-xs text-gray-500">{{ $d->producto->sku }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ $d->cantidad }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($d->precio_compra, 2) }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($d->cantidad * $d->precio_compra, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold text-gray-900">
                            <td class="px-4 py-3" colspan="3">Total</td>
                            <td class="px-4 py-3 text-right">${{ number_format($compra->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
