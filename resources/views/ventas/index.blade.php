<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ventas
            </h2>
            <a href="{{ route('ventas.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nueva venta
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-5 flex items-end gap-3">
                    <div class="flex-1">
                        <label for="fecha" class="block text-sm text-gray-500">Día</label>
                        <input id="fecha" type="date" name="fecha" value="{{ $fecha }}"
                               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Ver
                    </button>
                </form>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Vendido ese día</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($totalDia, 2) }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Número de ventas</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $cantidadDia }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Folio</th>
                            <th class="px-4 py-3">Hora</th>
                            <th class="px-4 py-3 text-right">Renglones</th>
                            <th class="px-4 py-3">Pago</th>
                            <th class="px-4 py-3">Atendió</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($ventas as $v)
                            <tr>
                                <td class="px-4 py-3">#{{ $v->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $v->fecha->format('H:i') }}</td>
                                <td class="px-4 py-3 text-right">{{ $v->detalles_count }}</td>
                                <td class="px-4 py-3 capitalize">{{ $v->metodo_pago }}</td>
                                <td class="px-4 py-3">{{ $v->user->name }}</td>
                                <td class="px-4 py-3 text-right font-medium">${{ number_format($v->total, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('ventas.show', $v) }}" class="text-indigo-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    No hay ventas registradas ese día.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $ventas->links() }}</div>
        </div>
    </div>
</x-app-layout>
