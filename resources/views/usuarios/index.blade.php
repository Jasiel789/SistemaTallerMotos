<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Usuarios
            </h2>
            <a href="{{ route('usuarios.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Nuevo usuario
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-900">
                        @foreach ($usuarios as $u)
                            <tr class="{{ $u->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3 font-medium">
                                    {{ $u->name }}
                                    @if ($u->id === auth()->id()) <span class="text-xs text-gray-500">(tú)</span> @endif
                                </td>
                                <td class="px-4 py-3">{{ $u->email }}</td>
                                <td class="px-4 py-3">
                                    @if ($u->rol === 'dueno')
                                        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">Dueño</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Empleado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $u->activo ? 'Activo' : 'Desactivado' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('usuarios.edit', $u) }}" class="text-indigo-600 hover:underline">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-500">
                Los usuarios no se borran para no perder el historial de ventas y compras; se desactivan.
            </p>
        </div>
    </div>
</x-app-layout>
