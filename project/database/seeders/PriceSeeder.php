<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Price;

class PriceSeeder extends Seeder
{
    /**
     * Создаём цены для всех продуктов без цен.
     * Диапазон: от 100 руб. до 50 000 руб.
     */
    public function run(): void
    {
        // Только продукты без цен (безопасно при повторном запуске)
        $products = Product::doesntHave('price')->get();

        foreach ($products as $product) {
            Price::create([
                'id_product' => $product->id,
                'price' => fake()->randomFloat(2, 100, 50000),
            ]);
        }
    }
}
