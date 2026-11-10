<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva orden de servicio
        </h2>
    </x-slot>

    @php
        $campo = 'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if ($clientes->isEmpty())
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Primero registra al cliente y su moto:
                    <a href="{{ route('clientes.create') }}" class="underline">+ Nuevo cliente</a>.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ordenes.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6"
                  x-data="{
                      clientes: @js($clientes),
                      clienteId: @js((string) old('cliente_id', $clienteId)),
                      motoId: @js((string) old('moto_id', '')),
                      km: @js((string) old('kilometraje', '')),
                      motos() {
                          const c = this.clientes.find(c => String(c.id) === this.clienteId);
                          return c ? c.motos : [];
                      },
                      elegirMoto() {
                          const m = this.motos().find(m => String(m.id) === this.motoId);
                          if (m && m.kilometraje !== null) { this.km = String(m.kilometraje); }
                      },
                      cambiarCliente() {
                          this.motoId = '';
                          this.km = '';
                          const m = this.motos();
                          if (m.length === 1) { this.motoId = String(m[0].id); this.elegirMoto(); }
                      }
                  }"
                  x-init="$nextTick(() => { if (clienteId && !motoId) { cambiarCliente(); } })">
                @csrf

                <div>
                    <x-input-label for="cliente_id" value="Cliente" />
                    <select id="cliente_id" name="cliente_id" x-model="clienteId" @change="cambiarCliente()" required class="{{ $campo }}">
                        <option value="">Elige un cliente…</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}@if ($c->telefono) — {{ $c->telefono }}@endif</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        ¿Cliente nuevo? <a href="{{ route('clientes.create') }}" class="text-indigo-600 hover:underline">Regístralo primero</a>.
                    </p>
                </div>

                <div x-show="clienteId" x-cloak>
                    <x-input-label for="moto_id" value="Moto" />
                    <select id="moto_id" name="moto_id" x-model="motoId" @change="elegirMoto()" required class="{{ $campo }}">
                        <option value="">Elige la moto…</option>
                        <template x-for="m in motos()" :key="m.id">
                            <option :value="m.id" :selected="String(m.id) === motoId"
                                    x-text="m.marca + ' ' + m.modelo + (m.anio ? ' ' + m.anio : '') + (m.placa ? ' · ' + m.placa : '')"></option>
                        </template>
                    </select>
                    <p x-show="motos().length === 0" class="mt-1 text-sm text-red-600">
                        Este cliente todavía no tiene motos. Agrégala desde su ficha.
                        <a :href="'{{ url('clientes') }}/' + clienteId" class="underline">Abrir ficha</a>
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="kilometraje" value="Kilometraje actual" />
                        <input id="kilometraje" name="kilometraje" type="number" min="0" x-model="km" class="{{ $campo }}">
                    </div>

                    <div>
                        <x-input-label for="mano_de_obra" value="Mano de obra (puedes dejarla en 0 y ponerla después)" />
                        <input id="mano_de_obra" name="mano_de_obra" type="number" min="0" step="0.01" value="{{ old('mano_de_obra', 0) }}" class="{{ $campo }}">
                    </div>
                </div>

                <div>
                    <x-input-label for="diagnostico" value="Falla reportada / diagnóstico" />
                    <textarea id="diagnostico" name="diagnostico" rows="4" class="{{ $campo }}"
                              placeholder="Ej. No arranca en frío, ruido en la cadena, cambio de balatas…">{{ old('diagnostico') }}</textarea>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Crear orden</x-primary-button>
                    <a href="{{ route('ordenes.index') }}" class="text-sm text-gray-500 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
