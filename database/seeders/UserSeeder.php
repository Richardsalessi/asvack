<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear los roles y permisos
        $this->call(RolesAndPermissionsSeeder::class);
        
        // Luego crear los usuarios con los roles ya existentes
        $this->call(UserSeeder::class);
    }
}

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $adminAccess = Permission::firstOrCreate(['name' => 'admin-access']);
        $providerAccess = Permission::firstOrCreate(['name' => 'provider-access']);
        $clientAccess = Permission::firstOrCreate(['name' => 'client-access']);

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $providerRole = Role::firstOrCreate(['name' => 'provider']);
        $clientRole = Role::firstOrCreate(['name' => 'client']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo($adminAccess);
        $providerRole->givePermissionTo($providerAccess);
        $clientRole->givePermissionTo($clientAccess);
    }
}

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
