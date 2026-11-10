<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar moto de {{ $moto->cliente->nombre }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('motos.update', $moto) }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')
                @include('motos._form')

                <div class="flex items-center gap-4">
                    <x-primary-button>Guardar cambios</x-primary-button>
                    <a href="{{ route('clientes.show', $moto->cliente_id) }}" class="text-sm text-gray-500 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
