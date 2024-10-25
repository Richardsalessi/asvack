<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario admin
        $admin = User::create([
            'name' => 'Richards',
            'email' => 'richards@gmail.com',
            'password' => bcrypt('proyecto2024'),
        ]);
        $admin->assignRole('admin');

        // Crear usuario proveedor
        $provider = User::create([
            'name' => 'Valentina',
            'email' => 'valentina@gmail.com',
            'password' => bcrypt('proyecto2024'),
        ]);
        $provider->assignRole('provider');

        // Crear usuario cliente
        $client = User::create([
            'name' => 'Cristian',
            'email' => 'cristian@gmail.com',
            'password' => bcrypt('proyecto2024'),
        ]);
        $client->assignRole('client');
    }
}
