<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buying = fake()->numberBetween(100, 5000);

        return [
            'sku' => strtoupper(Str::random(8)),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'buying_price' => $buying,
            'selling_price' => $buying + fake()->numberBetween(50, 2000),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'reorder_level' => 5,
            'barcode' => fake()->unique()->ean13(),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}
