<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
            <a href="{{ route('clientes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nuevo cliente
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center gap-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, teléfono, correo o placa…"
                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Buscar
                </button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3 text-right">Motos</th>
                            <th class="px-4 py-3 text-right">Órdenes</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($clientes as $c)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $c->nombre }}</td>
                                <td class="px-4 py-3">{{ $c->telefono ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ $c->motos_count }}</td>
                                <td class="px-4 py-3 text-right">{{ $c->ordenes_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('clientes.show', $c) }}" class="text-indigo-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">Todavía no hay clientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $clientes->links() }}</div>
        </div>
    </div>
</x-app-layout>
