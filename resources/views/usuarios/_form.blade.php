@php
    $editando = $usuario->exists;
    $esYo = $editando && $usuario->id === auth()->id();
    $campo = 'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $usuario->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Correo (con este entra al sistema)" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rol" value="Rol" />
        <select id="rol" name="rol" class="{{ $campo }}" @disabled($esYo)>
            <option value="empleado" @selected(old('rol', $usuario->rol) === 'empleado')>Empleado (vende y consulta stock)</option>
            <option value="dueno" @selected(old('rol', $usuario->rol) === 'dueno')>Dueño (acceso total)</option>
        </select>
        @if ($esYo)
            <input type="hidden" name="rol" value="dueno">
            <p class="mt-1 text-xs text-gray-500">No puedes cambiar tu propio rol.</p>
        @endif
        <x-input-error :messages="$errors->get('rol')" class="mt-2" />
    </div>

    @if ($editando && ! $esYo)
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                Usuario activo (si lo desmarcas, ya no puede entrar)
            </label>
        </div>
    @endif

    <div>
        <x-input-label for="password" :value="$editando ? 'Nueva contraseña (déjala vacía para no cambiarla)' : 'Contraseña'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $editando" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $editando" />
    </div>
</div>
