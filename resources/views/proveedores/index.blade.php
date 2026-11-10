<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Proveedores
            </h2>
            <a href="{{ route('proveedores.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nuevo proveedor
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

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center gap-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar proveedor…"
                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Buscar
                </button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Proveedor</th>
                            <th class="px-4 py-3">Contacto</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3 text-right">Productos</th>
                            <th class="px-4 py-3 text-right">Compras</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @forelse ($proveedores as $p)
                            <tr class="{{ $p->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3 font-medium">
                                    {{ $p->nombre }}
                                    @unless ($p->activo) <span class="ml-1 text-xs text-red-600">Inactivo</span> @endunless
                                </td>
                                <td class="px-4 py-3">{{ $p->contacto ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $p->telefono ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ $p->productos_count }}</td>
                                <td class="px-4 py-3 text-right">{{ $p->compras_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('proveedores.edit', $p) }}" class="text-indigo-600 hover:underline">Editar</a>
                                    <form method="POST" action="{{ route('proveedores.destroy', $p) }}" class="inline"
                                          onsubmit="return confirm('¿Eliminar {{ addslashes($p->nombre) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ml-3 text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    Aún no hay proveedores. Crea el primero para poder registrar compras.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $proveedores->links() }}</div>
        </div>
    </div>
</x-app-layout>
