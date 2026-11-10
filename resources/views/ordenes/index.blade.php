<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Órdenes de servicio
            </h2>
            <a href="{{ route('ordenes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nueva orden
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-center gap-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por cliente, teléfono, placa, modelo o folio…"
                       class="flex-1 min-w-[16rem] border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                <select name="estado" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="abiertas" @selected($estado === 'abiertas')>Abiertas</option>
                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                    @foreach (\App\Models\OrdenServicio::ETIQUETAS as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($estado === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>

                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Buscar
                </button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Folio</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Moto</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($ordenes as $o)
                            <tr>
                                <td class="px-4 py-3 font-medium">#{{ $o->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $o->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $o->cliente->nombre }}</td>
                                <td class="px-4 py-3">
                                    {{ $o->moto->marca }} {{ $o->moto->modelo }}
                                    @if ($o->moto->placa) <span class="text-xs text-gray-500">· {{ $o->moto->placa }}</span> @endif
                                </td>
                                <td class="px-4 py-3">@include('ordenes._badge', ['estado' => $o->estado])</td>
                                <td class="px-4 py-3 text-right">${{ number_format($o->total, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('ordenes.show', $o) }}" class="text-indigo-600 hover:underline">Abrir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay órdenes con ese filtro.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $ordenes->links() }}</div>
        </div>
    </div>
</x-app-layout>
