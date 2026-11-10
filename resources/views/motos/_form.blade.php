<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="marca" value="Marca" />
        <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full" :value="old('marca', $moto->marca)" placeholder="Ej. Italika, Honda, Yamaha" required />
        <x-input-error :messages="$errors->get('marca')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="modelo" value="Modelo" />
        <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full" :value="old('modelo', $moto->modelo)" placeholder="Ej. FT150" required />
        <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="anio" value="Año" />
        <x-text-input id="anio" name="anio" type="number" class="mt-1 block w-full" :value="old('anio', $moto->anio)" />
        <x-input-error :messages="$errors->get('anio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="placa" value="Placa" />
        <x-text-input id="placa" name="placa" type="text" class="mt-1 block w-full" :value="old('placa', $moto->placa)" />
        <x-input-error :messages="$errors->get('placa')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="kilometraje" value="Kilometraje" />
        <x-text-input id="kilometraje" name="kilometraje" type="number" min="0" class="mt-1 block w-full" :value="old('kilometraje', $moto->kilometraje)" />
        <x-input-error :messages="$errors->get('kilometraje')" class="mt-2" />
    </div>
</div>
