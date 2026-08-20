<?php

namespace Tests\Unit\Services;

use App\Models\Group;
use App\Models\Product;
use App\Models\Price;
use App\Services\CatalogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogServiceTest extends TestCase
{
    use RefreshDatabase;
    private CatalogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CatalogService();
    }

    /**
     * Тест: получение дерева групп
     * @return void
     */
    public function test_get_group_tree_returns_root_groups(): void
    {
        $group1 = Group::create([
            'name' => 'Test Group 1',
            'id_parent' => 0,
        ]);

        $group2 = Group::create([
            'name' => 'Test Group 2',
            'id_parent' => 0,
        ]);

        $result = $this->service->getGroupTree();

        $this->assertCount(2, $result);
        $this->assertEquals('Test Group 1', $result[0]['name']);
        $this->assertEquals('Test Group 2', $result[1]['name']);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('product_count', $result[0]);
    }

    /**
     * Тест: рекурсивное дерево с подгруппами
     * @return void
     */
    public function test_group_tree_contains_children(): void
    {
        $parent = Group::create([
            'name' => 'Parent Group',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child Group',
            'id_parent' => $parent->id,
        ]);

        $result = $this->service->getGroupTree();

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]['children']);
        $this->assertEquals('Child Group', $result[0]['children'][0]['name']);
    }

    /**
     * Тест: подсчёт товаров в группе
     * @return void
     */
    public function test_product_count_in_group(): void
    {
        $group = Group::create([
            'name' => 'Group with Products',
            'id_parent' => 0,
        ]);

        $product1 = Product::create([
            'name' => 'Product 1',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product1->id,
            'price' => 100.00,
        ]);

        $result = $this->service->getGroupTree();

        $this->assertEquals(1, $result[0]['product_count']);
    }

    /**
     * Тест: подсчёт товаров во вложенных группах
     * @return void
     */
    public function test_product_count_includes_children_groups(): void
    {
        $parent = Group::create([
            'name' => 'Parent',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child',
            'id_parent' => $parent->id,
        ]);

        $product = Product::create([
            'name' => 'Child Product',
            'id_group' => $child->id,
        ]);

        Price::create([
            'id_product' => $product->id,
            'price' => 50.00,
        ]);

        $result = $this->service->getGroupTree();

        $this->assertEquals(1, $result[0]['product_count']);
        $this->assertEquals(1, $result[0]['children'][0]['product_count']);
    }

    /**
     * Тест: получение товара по ID
     * @return void
     */
    public function test_get_product_returns_details(): void
    {
        $group = Group::create([
            'name' => 'Test Group',
            'id_parent' => 0,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'id_group' => $group->id,
        ]);

        $price = Price::create([
            'id_product' => $product->id,
            'price' => 999.99,
        ]);

        $result = $this->service->getProduct($product->id);

        $this->assertNotEmpty($result);
        $this->assertEquals('Test Product', $result['name']);
        $this->assertEquals(999.99, $result['price']);
        $this->assertArrayHasKey('breadcrumbs', $result);
    }

    /**
     * Тест: получение несуществующего товара
     * @return void
     */
    public function test_get_nonexistent_product_returns_false(): void
    {
        $result = $this->service->getProduct(999999);

        $this->assertFalse($result);
    }

    /**
     * Тест: хлебные крошки группы
     * @return void
     */
    public function test_get_group_breadcrumbs(): void
    {
        $parent = Group::create([
            'name' => 'Level 1',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Level 2',
            'id_parent' => $parent->id,
        ]);

        $breadcrumbs = $this->service->getGroupBreadcrumbs($child->id);

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals('Level 1', $breadcrumbs[0]['name']);
        $this->assertEquals('Level 2', $breadcrumbs[1]['name']);
    }

    /**
     * Тест: пустые хлебные крошки для несуществующей группы
     * @return void
     */
    public function test_get_group_breadcrumbs_for_nonexistent(): void
    {
        $breadcrumbs = $this->service->getGroupBreadcrumbs(999999);

        $this->assertEmpty($breadcrumbs);
    }

    /**
     * Тест: рекурсивное получение ID подгрупп
     * @return void
     */
    public function test_get_all_sub_group_ids_recursive(): void
    {
        $root = Group::create([
            'name' => 'Root',
            'id_parent' => 0,
        ]);

        $level1 = Group::create([
            'name' => 'Level 1',
            'id_parent' => $root->id,
        ]);

        $level2 = Group::create([
            'name' => 'Level 2',
            'id_parent' => $level1->id,
        ]);

        $ids = Group::getAllSubGroupIdsRecursive($root->id);

        $this->assertContains($root->id, $ids);
        $this->assertContains($level1->id, $ids);
        $this->assertContains($level2->id, $ids);
        $this->assertCount(3, $ids);
    }
}
