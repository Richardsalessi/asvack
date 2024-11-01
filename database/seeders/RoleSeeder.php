<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos específicos para roles
        $adminAccess = Permission::firstOrCreate(['name' => 'admin-access']);
        $providerAccess = Permission::firstOrCreate(['name' => 'provider-access']);

        // Crear roles de admin y proveedor
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $providerRole = Role::firstOrCreate(['name' => 'provider']);

        // Asignar permisos a cada rol
        $adminRole->givePermissionTo([$adminAccess]);
        $providerRole->givePermissionTo([$providerAccess]);
    }
}
