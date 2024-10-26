<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el usuario admin ya existe
        $admin = User::firstOrCreate(
            ['email' => 'richards@gmail.com'],
            [
                'name' => 'Richards',
                'password' => bcrypt('proyecto2024'),
            ]
        );
        $admin->assignRole('admin');

        // Verificar si el usuario proveedor ya existe
        $provider = User::firstOrCreate(
            ['email' => 'valentina@gmail.com'],
            [
                'name' => 'Valentina',
                'password' => bcrypt('proyecto2024'),
            ]
        );
        $provider->assignRole('provider');

        // Verificar si el usuario cliente ya existe
        $client = User::firstOrCreate(
            ['email' => 'cristian@gmail.com'],
            [
                'name' => 'Cristian',
                'password' => bcrypt('proyecto2024'),
            ]
        );
        $client->assignRole('client');
    }
}
    