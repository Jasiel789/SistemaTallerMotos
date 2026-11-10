<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes de ventas y ganancia
        </h2>
    </x-slot>

    @php
        $money = fn ($n) => '$' . number_format((float) $n, 2);
        $hoy = now()->toDateString();
        $rangos = [
            'Hoy' => [$hoy, $hoy],
            'Esta semana' => [now()->startOfWeek()->toDateString(), $hoy],
            'Este mes' => [now()->startOfMonth()->toDateString(), $hoy],
            'Mes pasado' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtro --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-5 space-y-3">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label for="desde" class="block text-sm text-gray-500">Desde</label>
                        <input id="desde" type="date" name="desde" value="{{ $desde }}"
                               class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="hasta" class="block text-sm text-gray-500">Hasta</label>
                        <input id="hasta" type="date" name="hasta" value="{{ $hasta }}"
                               class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Ver reporte
                    </button>
                </form>

                <div class="flex flex-wrap gap-3 text-sm">
                    @foreach ($rangos as $nombre => [$d, $h])
                        <a href="{{ route('reportes.index', ['desde' => $d, 'hasta' => $h]) }}" class="text-indigo-600 hover:underline">{{ $nombre }}</a>
                    @endforeach
                </div>

                @error('hasta')
                    <p class="text-sm text-red-600">La fecha final no puede ser anterior a la inicial.</p>
                @enderror
            </div>

            {{-- Resumen --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Vendido</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $money($ingresos) }}</p>
                    <p class="text-xs text-gray-500">{{ $numVentas }} {{ $numVentas === 1 ? 'venta' : 'ventas' }} · mano de obra {{ $money($manoObra) }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Costo de lo vendido</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $money($costo) }}</p>
                    <p class="text-xs text-gray-500">Lo que te costó, al precio de ese día</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Ganancia</p>
                    <p class="mt-1 text-2xl font-semibold {{ $ganancia < 0 ? 'text-red-600' : 'text-green-700' }}">{{ $money($ganancia) }}</p>
                    <p class="text-xs text-gray-500">Incluye mano de obra</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Margen</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($margen, 1) }}%</p>
                    <p class="text-xs text-gray-500">Ganancia sobre lo vendido</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Más rentables --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Lo que más ganancia deja</h3>
                    @if ($topProductos->isEmpty())
                        <p class="mt-3 text-sm text-gray-500">Sin ventas en este periodo.</p>
                    @else
                        <table class="mt-3 w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Producto</th>
                                    <th class="py-2 text-right">Piezas</th>
                                    <th class="py-2 text-right">Vendido</th>
                                    <th class="py-2 text-right">Ganancia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($topProductos as $t)
                                    <tr>
                                        <td class="py-2">
                                            {{ $t->nombre }}
                                            <span class="text-xs text-gray-500">{{ $t->sku }}</span>
                                        </td>
                                        <td class="py-2 text-right">{{ $t->piezas }}</td>
                                        <td class="py-2 text-right">{{ $money($t->ingresos) }}</td>
                                        <td class="py-2 text-right font-medium">{{ $money($t->ganancia) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Por día --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Por día</h3>
                    @if ($porDia->isEmpty())
                        <p class="mt-3 text-sm text-gray-500">Sin ventas en este periodo.</p>
                    @else
                        <table class="mt-3 w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Día</th>
                                    <th class="py-2 text-right">Ventas</th>
                                    <th class="py-2 text-right">Vendido</th>
                                    <th class="py-2 text-right">Ganancia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($porDia as $d)
                                    <tr>
                                        <td class="py-2">{{ \Illuminate\Support\Carbon::parse($d->dia)->format('d/m/Y') }}</td>
                                        <td class="py-2 text-right">{{ $d->ventas }}</td>
                                        <td class="py-2 text-right">{{ $money($d->ingresos) }}</td>
                                        <td class="py-2 text-right font-medium">{{ $money($d->ganancia) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Por método de pago --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Por método de pago</h3>
                    @if ($porPago->isEmpty())
                        <p class="mt-3 text-sm text-gray-500">Sin ventas en este periodo.</p>
                    @else
                        <table class="mt-3 w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Método</th>
                                    <th class="py-2 text-right">Ventas</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($porPago as $p)
                                    <tr>
                                        <td class="py-2 capitalize">{{ $p->metodo_pago }}</td>
                                        <td class="py-2 text-right">{{ $p->ventas }}</td>
                                        <td class="py-2 text-right font-medium">{{ $money($p->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Por empleado --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Por quién vendió</h3>
                    @if ($porEmpleado->isEmpty())
                        <p class="mt-3 text-sm text-gray-500">Sin ventas en este periodo.</p>
                    @else
                        <table class="mt-3 w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Usuario</th>
                                    <th class="py-2 text-right">Ventas</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($porEmpleado as $e)
                                    <tr>
                                        <td class="py-2">{{ $e->name }}</td>
                                        <td class="py-2 text-right">{{ $e->ventas }}</td>
                                        <td class="py-2 text-right font-medium">{{ $money($e->total) }}</td>
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
