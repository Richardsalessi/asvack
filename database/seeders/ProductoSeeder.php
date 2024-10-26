<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        Producto::create([
            'nombre' => 'Producto 1',
            'descripcion' => 'Descripción del Producto 1',
            'precio' => 100.00,
            'categoria' => 'Electrónica',
            'imagen' => 'imagenes/producto1.jpg',
        ]);

        Producto::create([
            'nombre' => 'Producto 2',
            'descripcion' => 'Descripción del Producto 2',
            'precio' => 200.00,
            'categoria' => 'Mecánica',
            'imagen' => 'imagenes/producto2.jpg',
        ]);
    }
}
