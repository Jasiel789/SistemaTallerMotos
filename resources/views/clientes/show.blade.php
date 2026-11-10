<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $cliente->nombre }}</h2>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('clientes.edit', $cliente) }}" class="text-indigo-600 hover:underline">Editar datos</a>
                <a href="{{ route('clientes.index') }}" class="text-gray-500 hover:underline">← Clientes</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @error('moto')
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
            @enderror

            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-wrap items-center justify-between gap-4">
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">Teléfono</dt>
                        <dd class="font-medium text-gray-900">{{ $cliente->telefono ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Correo</dt>
                        <dd class="font-medium text-gray-900">{{ $cliente->email ?: '—' }}</dd>
                    </div>
                </dl>

                @if ($cliente->motos->isNotEmpty())
                    <a href="{{ route('ordenes.create', ['cliente_id' => $cliente->id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                        + Nueva orden de servicio
                    </a>
                @endif
            </div>

            {{-- Motos --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Motos</h3>

                @if ($cliente->motos->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Este cliente todavía no tiene motos registradas. Agrega la primera abajo.</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Moto</th>
                                    <th class="py-2">Año</th>
                                    <th class="py-2">Placa</th>
                                    <th class="py-2 text-right">Km</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($cliente->motos as $m)
                                    <tr>
                                        <td class="py-2 font-medium">{{ $m->marca }} {{ $m->modelo }}</td>
                                        <td class="py-2">{{ $m->anio ?? '—' }}</td>
                                        <td class="py-2">{{ $m->placa ?: '—' }}</td>
                                        <td class="py-2 text-right">{{ $m->kilometraje !== null ? number_format($m->kilometraje) : '—' }}</td>
                                        <td class="py-2 text-right whitespace-nowrap">
                                            <a href="{{ route('motos.edit', $m) }}" class="text-indigo-600 hover:underline">Editar</a>
                                            <form method="POST" action="{{ route('motos.destroy', $m) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta moto?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="ml-3 text-red-600 hover:underline">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <form method="POST" action="{{ route('clientes.motos.store', $cliente) }}" class="mt-6 border-t border-gray-100 pt-6 space-y-6">
                    @csrf
                    <h4 class="text-sm font-medium text-gray-900">Agregar moto</h4>
                    @include('motos._form', ['moto' => new \App\Models\Moto()])
                    <x-primary-button>Agregar moto</x-primary-button>
                </form>
            </div>

            {{-- Últimas órdenes --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Últimas órdenes</h3>

                @if ($ordenes->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Sin órdenes todavía.</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-2">Folio</th>
                                    <th class="py-2">Fecha</th>
                                    <th class="py-2">Moto</th>
                                    <th class="py-2">Estado</th>
                                    <th class="py-2 text-right">Total</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-900">
                                @foreach ($ordenes as $o)
                                    <tr>
                                        <td class="py-2">#{{ $o->id }}</td>
                                        <td class="py-2 whitespace-nowrap">{{ $o->fecha->format('d/m/Y') }}</td>
                                        <td class="py-2">{{ $o->moto->marca }} {{ $o->moto->modelo }}</td>
                                        <td class="py-2">@include('ordenes._badge', ['estado' => $o->estado])</td>
                                        <td class="py-2 text-right">${{ number_format($o->total, 2) }}</td>
                                        <td class="py-2 text-right"><a href="{{ route('ordenes.show', $o) }}" class="text-indigo-600 hover:underline">Abrir</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
