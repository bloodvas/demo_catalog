<?php

namespace Tests\Unit\Models;

use App\Models\Group;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GroupModelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Тест: связь products
     * @return void
     */
    public function test_group_has_products_relation(): void
    {
        $group = Group::create([
            'name' => 'Test Group',
            'id_parent' => 0,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'id_group' => $group->id,
        ]);

        $this->assertTrue($group->products()->where('id', $product->id)->exists());
    }

    /**
     * Тест: связь children
     * @return void
     */
    public function test_group_has_children_relation(): void
    {
        $parent = Group::create([
            'name' => 'Parent',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child',
            'id_parent' => $parent->id,
        ]);

        $this->assertTrue($parent->children()->where('id', $child->id)->exists());
    }

    /**
     * Тест: связь parent
     * @return void
     */
    public function test_group_has_parent_relation(): void
    {
        $parent = Group::create([
            'name' => 'Parent',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child',
            'id_parent' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $child->parent->id);
    }

    /**
     * Тест: все подгруппы для группы
     * @return void
     */
    public function test_get_all_sub_group_ids(): void
    {
        $root = Group::create([
            'name' => 'Root',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child',
            'id_parent' => $root->id,
        ]);

        $ids = $root->getAllSubGroupIds();

        $this->assertContains($root->id, $ids);
        $this->assertContains($child->id, $ids);
    }

    /**
     * Тест: подсчёт товаров в группе
     * @return void
     */
    public function test_count_products_in_group(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $product = Product::create([
                'name' => "Product {$i}",
                'id_group' => $group->id,
            ]);

            Price::create([
                'id_product' => $product->id,
                'price' => 100.00 + $i,
            ]);
        }

        $count = DB::table('products')->where('id_group', $group->id)->count();

        $this->assertEquals(3, $count);
    }

    /**
     * Тест: подсчёт без товаров
     * @return void
     */
    public function test_no_products_in_empty_group(): void
    {
        $group = Group::create([
            'name' => 'Empty Group',
            'id_parent' => 0,
        ]);

        $count = DB::table('products')->where('id_group', $group->id)->count();

        $this->assertEquals(0, $count);
    }
}
