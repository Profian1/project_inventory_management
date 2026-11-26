<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed 20 sample products across the default categories for demo.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $products = [
            ['name' => 'USB Cable', 'category' => 'Cables', 'buying' => 100, 'selling' => 200, 'stock' => 45],
            ['name' => 'Type-C Cable', 'category' => 'Cables', 'buying' => 150, 'selling' => 300, 'stock' => 60],
            ['name' => 'iPhone Charger', 'category' => 'Chargers', 'buying' => 500, 'selling' => 900, 'stock' => 3],
            ['name' => 'Samsung Fast Charger', 'category' => 'Chargers', 'buying' => 600, 'selling' => 1100, 'stock' => 0],
            ['name' => 'Wireless Mouse', 'category' => 'Accessories', 'buying' => 500, 'selling' => 800, 'stock' => 2],
            ['name' => 'Wired Keyboard', 'category' => 'Accessories', 'buying' => 700, 'selling' => 1200, 'stock' => 0],
            ['name' => 'TP-Link Router', 'category' => 'Routers', 'buying' => 2500, 'selling' => 4000, 'stock' => 12],
            ['name' => 'MiFi Router 4G', 'category' => 'Routers', 'buying' => 3500, 'selling' => 5500, 'stock' => 8],
            ['name' => '32GB Flash Drive', 'category' => 'Electronics', 'buying' => 400, 'selling' => 700, 'stock' => 30],
            ['name' => 'Bluetooth Speaker', 'category' => 'Electronics', 'buying' => 1500, 'selling' => 2500, 'stock' => 1],
            ['name' => 'Earphones', 'category' => 'Accessories', 'buying' => 200, 'selling' => 400, 'stock' => 50],
            ['name' => 'Power Bank 10000mAh', 'category' => 'Electronics', 'buying' => 1800, 'selling' => 3000, 'stock' => 6],
            ['name' => 'HDMI Cable', 'category' => 'Cables', 'buying' => 300, 'selling' => 600, 'stock' => 0],
            ['name' => 'Ethernet Cable', 'category' => 'Cables', 'buying' => 250, 'selling' => 500, 'stock' => 25],
            ['name' => 'Car Charger', 'category' => 'Chargers', 'buying' => 350, 'selling' => 650, 'stock' => 4],
            ['name' => 'Wall Adapter', 'category' => 'Chargers', 'buying' => 200, 'selling' => 400, 'stock' => 40],
            ['name' => 'Laptop Charger', 'category' => 'Chargers', 'buying' => 1500, 'selling' => 2500, 'stock' => 9],
            ['name' => '1TB External HDD', 'category' => 'Electronics', 'buying' => 6500, 'selling' => 9000, 'stock' => 5],
            ['name' => 'Webcam', 'category' => 'Electronics', 'buying' => 1200, 'selling' => 2000, 'stock' => 3],
            ['name' => 'Phone Case', 'category' => 'Accessories', 'buying' => 150, 'selling' => 350, 'stock' => 70],
        ];

        foreach ($products as $i => $p) {
            Product::updateOrCreate(
                ['sku' => 'PRD-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $p['name'],
                    'category_id' => $categories[$p['category']] ?? null,
                    'buying_price' => $p['buying'],
                    'selling_price' => $p['selling'],
                    'stock_quantity' => $p['stock'],
                    'reorder_level' => 5,
                    'is_active' => true,
                ]
            );
        }
    }
}
