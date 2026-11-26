<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the default product categories.
     */
    public function run(): void
    {
        foreach (['Accessories', 'Routers', 'Electronics', 'Cables', 'Chargers'] as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
