<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Venta #{{ $venta->id }}
            </h2>
            <a href="{{ route('ventas.index') }}" class="text-sm text-gray-500 hover:underline">← Volver a ventas</a>
        </div>
    </x-slot>

    <style>
        @media print {
            nav, header, .no-imprimir { display: none !important; }
            body { background: #fff !important; }
        }
    </style>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="no-imprimir rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-gray-500">Folio</dt>
                        <dd class="font-medium text-gray-900">#{{ $venta->id }}</dd>
                        @if ($venta->orden_id)
                            <dd><a href="{{ route('ordenes.show', $venta->orden_id) }}" class="text-xs text-indigo-600 hover:underline">Orden de servicio #{{ $venta->orden_id }}</a></dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-gray-500">Fecha</dt>
                        <dd class="font-medium text-gray-900">{{ $venta->fecha->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Pago</dt>
                        <dd class="font-medium capitalize text-gray-900">{{ $venta->metodo_pago }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Atendió</dt>
                        <dd class="font-medium text-gray-900">{{ $venta->user->name }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3 text-right">Cant.</th>
                            <th class="px-4 py-3 text-right">Precio</th>
                            <th class="px-4 py-3 text-right">Importe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @foreach ($venta->detalles as $d)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $d->producto->nombre }}
                                    <span class="text-xs text-gray-500">{{ $d->producto->sku }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ $d->cantidad }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($d->precio_venta, 2) }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($d->cantidad * $d->precio_venta, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if ($venta->mano_de_obra > 0)
                            <tr class="border-t border-gray-200 text-gray-900">
                                <td class="px-4 py-3" colspan="3">Mano de obra</td>
                                <td class="px-4 py-3 text-right">${{ number_format($venta->mano_de_obra, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-t border-gray-200 text-lg font-semibold text-gray-900">
                            <td class="px-4 py-3" colspan="3">Total</td>
                            <td class="px-4 py-3 text-right">${{ number_format($venta->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="no-imprimir flex items-center gap-4">
                <a href="{{ route('ventas.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    + Nueva venta
                </a>
                <button type="button" onclick="window.print()" class="text-sm text-gray-500 hover:underline">Imprimir</button>
            </div>
        </div>
    </div>
</x-app-layout>
