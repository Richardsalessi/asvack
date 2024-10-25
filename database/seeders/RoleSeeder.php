<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'provider']);
        Role::create(['name' => 'client']);
    }
}
