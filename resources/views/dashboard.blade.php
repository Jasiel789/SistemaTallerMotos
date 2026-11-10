<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel del taller
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Motos en el taller</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $ordenesAbiertasCount }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Ventas de hoy</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($ventasHoy, 2) }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Con stock bajo</p>
                    <p class="mt-1 text-2xl font-semibold {{ $stockBajoCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $stockBajoCount }}
                    </p>
                    <p class="text-xs text-gray-500">de {{ $totalProductos }} productos activos</p>
                </div>

                @if ($esDueno)
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">Inventario (a precio de compra)</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($valorCosto, 2) }}</p>
                        <p class="text-xs text-gray-500">A precio de venta: ${{ number_format($valorVenta, 2) }}</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('ordenes.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    + Nueva orden
                </a>
                <a href="{{ route('ventas.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    + Nueva venta
                </a>
                @if ($esDueno)
                    <a href="{{ route('compras.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        + Nueva compra
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Motos en el taller</h3>
                        <a href="{{ route('ordenes.index') }}" class="text-sm text-indigo-600 hover:underline">Ver órdenes</a>
                    </div>

                    @if ($ordenesAbiertas->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">No hay motos en reparación.</p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-100">
                            @foreach ($ordenesAbiertas as $o)
                                <li>
                                    <a href="{{ route('ordenes.show', $o) }}" class="flex items-center justify-between gap-3 py-3 hover:bg-gray-50">
                                        <span class="text-sm">
                                            <span class="block font-medium text-gray-900">
                                                #{{ $o->id }} · {{ $o->moto->marca }} {{ $o->moto->modelo }}
                                            </span>
                                            <span class="block text-xs text-gray-500">{{ $o->cliente->nombre }} · desde {{ $o->fecha->format('d/m') }}</span>
                                        </span>
                                        @include('ordenes._badge', ['estado' => $o->estado])
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Por resurtir</h3>
                        <a href="{{ route('productos.index', ['bajo' => 1]) }}" class="text-sm text-indigo-600 hover:underline">Ver todos</a>
                    </div>

                    @if ($stockBajo->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">No hay productos con stock bajo.</p>
                    @else
                        <table class="mt-4 w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2">Producto</th>
                                    <th class="py-2 text-right">Stock</th>
                                    <th class="py-2 text-right">Mínimo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($stockBajo as $p)
                                    <tr>
                                        <td class="py-2">
                                            @if ($esDueno)
                                                <a href="{{ route('productos.edit', $p) }}" class="hover:underline">{{ $p->nombre }}</a>
                                            @else
                                                {{ $p->nombre }}
                                            @endif
                                            <span class="text-xs text-gray-500">{{ $p->sku }}</span>
                                        </td>
                                        <td class="py-2 text-right font-semibold text-red-600">{{ $p->stock_actual }}</td>
                                        <td class="py-2 text-right">{{ $p->stock_minimo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
