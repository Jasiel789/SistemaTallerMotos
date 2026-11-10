<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar producto
        </h2>
    </x-slot>

    @php
        $preciosProveedores = $producto->proveedores;
        $mejorPrecio = $preciosProveedores->isNotEmpty()
            ? $preciosProveedores->min(fn ($pr) => (float) $pr->pivot->precio_compra)
            : null;
    @endphp

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('productos.update', $producto) }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')
                @include('productos._form')

                <div class="flex items-center gap-4">
                    <x-primary-button>Guardar cambios</x-primary-button>
                    <a href="{{ route('productos.index') }}" class="text-sm text-gray-500 hover:underline">Cancelar</a>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Precios por proveedor</h3>

                @if ($preciosProveedores->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">
                        Aún no hay compras de este producto. Cuando lo compres a un proveedor, aquí verás cuánto te cobra cada uno.
                    </p>
                @else
                    <table class="mt-3 w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Proveedor</th>
                                <th class="py-2 text-right">Último precio</th>
                                <th class="py-2 text-right">Fecha</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-900">
                            @foreach ($preciosProveedores->sortBy(fn ($pr) => (float) $pr->pivot->precio_compra) as $pr)
                                <tr>
                                    <td class="py-2">{{ $pr->nombre }}</td>
                                    <td class="py-2 text-right">${{ number_format($pr->pivot->precio_compra, 2) }}</td>
                                    <td class="py-2 text-right">
                                        {{ $pr->pivot->fecha_precio ? \Illuminate\Support\Carbon::parse($pr->pivot->fecha_precio)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="py-2 text-right">
                                        @if ((float) $pr->pivot->precio_compra === (float) $mejorPrecio && $preciosProveedores->count() > 1)
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Más barato</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
