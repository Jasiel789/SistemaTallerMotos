<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Compras a proveedores
            </h2>
            <a href="{{ route('compras.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nueva compra
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

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Proveedor</th>
                            <th class="px-4 py-3">Folio</th>
                            <th class="px-4 py-3 text-right">Renglones</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3">Registró</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($compras as $c)
                            <tr>
                                <td class="px-4 py-3">{{ $c->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $c->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $c->proveedor->nombre }}</td>
                                <td class="px-4 py-3">{{ $c->folio ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ $c->detalles_count }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($c->total, 2) }}</td>
                                <td class="px-4 py-3">{{ $c->user->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('compras.show', $c) }}" class="text-indigo-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                    Todavía no hay compras registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $compras->links() }}</div>
        </div>
    </div>
</x-app-layout>
