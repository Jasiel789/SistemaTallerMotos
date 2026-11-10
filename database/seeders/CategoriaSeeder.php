<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Nombre => margen de ganancia sugerido (%)
        $categorias = [
            'Frenos' => 40,
            'Aceites y lubricantes' => 25,
            'Llantas y cámaras' => 25,
            'Eléctrico y baterías' => 35,
            'Transmisión (cadena y sprockets)' => 35,
            'Filtros' => 40,
            'Motor' => 35,
            'Suspensión' => 35,
            'Carrocería y accesorios' => 45,
            'Tornillería y consumibles' => 50,
            'Otros' => 30,
        ];

        foreach ($categorias as $nombre => $margen) {
            Categoria::firstOrCreate(['nombre' => $nombre], ['margen_porcentaje' => $margen]);
        }
    }
}
