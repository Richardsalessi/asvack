<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminAccess = Permission::firstOrCreate(['name' => 'admin-access']);
        $providerAccess = Permission::firstOrCreate(['name' => 'provider-access']);
        $adminProviderAccess = Permission::firstOrCreate(['name' => 'admin-provider-access']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $providerRole = Role::firstOrCreate(['name' => 'provider']);

        $adminRole->givePermissionTo([$adminAccess, $adminProviderAccess]);
        $providerRole->givePermissionTo([$providerAccess, $adminProviderAccess]);
    }
}

