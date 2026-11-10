@php
    $esDueno = auth()->user()->rol === 'dueno';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>
            @if ($esDueno)
                <a href="{{ route('productos.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    + Nuevo producto
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-center gap-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, código, marca o modelo de moto…"
                       class="flex-1 min-w-[16rem] border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="bajo" value="1" @checked(request()->boolean('bajo')) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    Solo stock bajo
                </label>

                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Buscar
                </button>

                @if (request()->hasAny(['q', 'bajo']))
                    <a href="{{ route('productos.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
                @endif
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3 text-right">Stock</th>
                            @if ($esDueno)
                                <th class="px-4 py-3 text-right">Compra</th>
                            @endif
                            <th class="px-4 py-3 text-right">Venta</th>
                            @if ($esDueno)
                                <th class="px-4 py-3 text-right">Ganancia</th>
                                <th class="px-4 py-3"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($productos as $p)
                            <tr class="{{ $p->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $p->sku }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $p->nombre }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $p->marca }}
                                        @if ($p->ubicacion) · {{ $p->ubicacion }} @endif
                                        @unless ($p->activo) · <span class="text-red-600">Inactivo</span> @endunless
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $p->categoria?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $p->stock_actual <= $p->stock_minimo ? 'text-red-600' : '' }}">
                                    {{ $p->stock_actual }}
                                </td>
                                @if ($esDueno)
                                    <td class="px-4 py-3 text-right">${{ number_format($p->precio_compra, 2) }}</td>
                                @endif
                                <td class="px-4 py-3 text-right">${{ number_format($p->precio_venta, 2) }}</td>
                                @if ($esDueno)
                                    <td class="px-4 py-3 text-right">${{ number_format($p->precio_venta - $p->precio_compra, 2) }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('productos.edit', $p) }}" class="text-indigo-600 hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('productos.destroy', $p) }}" class="inline"
                                              onsubmit="return confirm('¿Eliminar {{ addslashes($p->nombre) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ml-3 text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $esDueno ? 8 : 5 }}" class="px-4 py-10 text-center text-gray-500">
                                    No hay productos todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $productos->links() }}</div>
        </div>
    </div>
</x-app-layout>
