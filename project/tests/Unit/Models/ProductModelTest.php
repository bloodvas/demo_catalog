<?php

namespace Tests\Unit\Models;

use App\Models\Group;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Тест: связь price (hasOne)
     * @return void
     */
    public function test_product_has_one_price_relation(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'id_group' => 1,
        ]);

        $price = Price::create([
            'id_product' => $product->id,
            'price' => 100.00,
        ]);

        $this->assertTrue($product->price()->where('id', $price->id)->exists());
    }

    /**
     * Тест: связь group (belongsTo)
     * @return void
     */
    public function test_product_belongs_to_group_relation(): void
    {
        $group = Group::create([
            'name' => 'Test Group',
            'id_parent' => 0,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'id_group' => $group->id,
        ]);

        $this->assertEquals($group->id, $product->group->id);
    }

    /**
     * Тест: создание продукта с ценой
     * @return void
     */
    public function test_product_with_price(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product->id,
            'price' => 1234.56,
        ]);

        $loaded = Product::with('price')->find($product->id);

        $this->assertNotNull($loaded->price);
        $this->assertEquals(1234.56, $loaded->price->price);
    }
}
