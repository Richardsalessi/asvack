<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
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
