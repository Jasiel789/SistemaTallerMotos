@php
    $editando = $proveedor->exists;
    $campo = 'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="nombre" value="Nombre del proveedor" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $proveedor->nombre)" required />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contacto" value="Persona de contacto" />
        <x-text-input id="contacto" name="contacto" type="text" class="mt-1 block w-full" :value="old('contacto', $proveedor->contacto)" />
        <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono / WhatsApp" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono', $proveedor->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="email" value="Correo" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $proveedor->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notas" value="Notas (días de entrega, condiciones de pago, etc.)" />
        <textarea id="notas" name="notas" rows="3" class="{{ $campo }}">{{ old('notas', $proveedor->notas) }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>

    @if ($editando)
        <div class="sm:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $proveedor->activo)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                Proveedor activo
            </label>
        </div>
    @endif
</div>
