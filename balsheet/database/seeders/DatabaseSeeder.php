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
        // Seed the default administrator, categories, settings and demo products.
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
