<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear los roles
        $this->call(RoleSeeder::class);
        
        // Luego crear los usuarios con los roles ya existentes
        $this->call(UserSeeder::class);
    }
}
