<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        // Crear algunas categorías
        $electronica = Categoria::firstOrCreate(['nombre' => 'Electrónica', 'descripcion' => 'Productos electrónicos']);
        $mecanica = Categoria::firstOrCreate(['nombre' => 'Mecánica', 'descripcion' => 'Productos mecánicos']);

        // Crear productos asociados a las categorías
        Producto::create([
            'nombre' => 'Producto 1',
            'descripcion' => 'Descripción del Producto 1',
            'precio' => 100.00,
            'imagen' => 'imagenes/producto1.jpg',
            'categoria_id' => $electronica->id,
        ]);

        Producto::create([
            'nombre' => 'Producto 2',
            'descripcion' => 'Descripción del Producto 2',
            'precio' => 200.00,
            'imagen' => 'imagenes/producto2.jpg',
            'categoria_id' => $mecanica->id,
        ]);
    }
}
