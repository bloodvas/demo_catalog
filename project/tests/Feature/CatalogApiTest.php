<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: GET /api/groups возвращает список групп
     * @return void
     */
    public function test_get_groups_returns_success(): void
    {
        Group::create([
            'name' => 'Test Group',
            'id_parent' => 0,
        ]);

        $response = $this->getJson('/api/groups');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Test Group']);
    }

    /**
     * Тест: GET /api/groups возвращает дерево с product_count
     * @return void
     */
    public function test_groups_include_product_count(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        $product = Product::create([
            'name' => 'Product',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product->id,
            'price' => 100.00,
        ]);

        $response = $this->getJson('/api/groups');

        $response->assertStatus(200)
            ->assertJsonFragment(['product_count' => 1]);
    }

    /**
     * Тест: GET /api/groups/{id}/breadcrumbs
     * @return void
     */
    public function test_group_breadcrumbs_returns_success(): void
    {
        $parent = Group::create([
            'name' => 'Parent',
            'id_parent' => 0,
        ]);

        $child = Group::create([
            'name' => 'Child',
            'id_parent' => $parent->id,
        ]);

        $response = $this->getJson("/api/groups/{$child->id}/breadcrumbs");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Parent'])
            ->assertJsonFragment(['name' => 'Child']);
    }

    /**
     * Тест: GET /api/groups/{id}/breadcrumbs для несуществующей группы
     * @return void
     */
    public function test_group_breadcrumbs_for_nonexistent_returns_404(): void
    {
        $response = $this->getJson('/api/groups/999999/breadcrumbs');

        $response->assertStatus(404)
            ->assertJsonFragment(['error' => 'Group not found']);
    }

    /**
     * Тест: POST /api/products без фильтров
     * @return void
     */
    public function test_products_returns_paginated_list(): void
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
            'price' => 100.00,
        ]);

        $response = $this->postJson('/api/products', [
            'page' => 1,
            'per_page' => 12,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name']],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);
    }

    /**
     * Тест: POST /api/products с сортировкой
     * @return void
     */
    public function test_products_with_sorting(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        $product1 = Product::create([
            'name' => 'Alpha',
            'id_group' => $group->id,
        ]);

        Price::create(['id_product' => $product1->id, 'price' => 100.00]);

        $product2 = Product::create([
            'name' => 'Zebra',
            'id_group' => $group->id,
        ]);

        Price::create(['id_product' => $product2->id, 'price' => 200.00]);

        $response = $this->postJson('/api/products', [
            'sort' => ['field' => 'name', 'direction' => 'asc'],
            'page' => 1,
            'per_page' => 12,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']);
        $this->assertEquals('Zebra', $data[1]['name']);
    }

    /**
     * Тест: POST /api/products без фильтра - все продукты
     * @return void
     */
    public function test_products_without_filter_returns_all(): void
    {
        Group::create(['name' => 'Group 1', 'id_parent' => 0]);
        Group::create(['name' => 'Group 2', 'id_parent' => 0]);

        $product1 = Product::create([
            'name' => 'Product 1',
            'id_group' => 1,
        ]);

        Price::create(['id_product' => $product1->id, 'price' => 100.00]);

        $product2 = Product::create([
            'name' => 'Product 2',
            'id_group' => 2,
        ]);

        Price::create(['id_product' => $product2->id, 'price' => 200.00]);

        $response = $this->postJson('/api/products', [
            'page' => 1,
            'per_page' => 50,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /**
     * Тест: GET /api/products/{id} возвращает товар
     * @return void
     */
    public function test_get_product_by_id(): void
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
            'price' => 500.00,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Test Product',
                'price' => 500.00,
            ])
            ->assertJsonStructure(['breadcrumbs']);
    }

    /**
     * Тест: GET /api/products/{id} для несуществующего товара
     * @return void
     */
    public function test_get_nonexistent_product_returns_404(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404)
            ->assertJsonFragment(['error' => 'Product not found']);
    }

    /**
     * Тест: валидация запроса с неверной сортировкой
     * @return void
     */
    public function test_products_with_invalid_sort_field_fails(): void
    {
        $response = $this->postJson('/api/products', [
            'sort' => ['field' => 'invalid', 'direction' => 'asc'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort.field']);
    }

    /**
     * Тест: валидация запроса с неверным направлением
     * @return void
     */
    public function test_products_with_invalid_sort_direction_fails(): void
    {
        $response = $this->postJson('/api/products', [
            'sort' => ['field' => 'price', 'direction' => 'invalid'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort.direction']);
    }

    /**
     * Тест: фильтрация по цене
     * @return void
     */
    public function test_products_filtered_by_price(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        $product1 = Product::create([
            'name' => 'Cheap',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product1->id,
            'price' => 10.00,
        ]);

        $product2 = Product::create([
            'name' => 'Expensive',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product2->id,
            'price' => 150.00,
        ]);

        $product3 = Product::create([
            'name' => 'Very Expensive',
            'id_group' => $group->id,
        ]);

        Price::create([
            'id_product' => $product3->id,
            'price' => 500.00,
        ]);

        $response = $this->postJson('/api/products', [
            'price' => ['min' => 100, 'max' => 200],
            'page' => 1,
            'per_page' => 12,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Expensive', $data[0]['name']);
    }

    /**
     * Тест: фильтрация по названию
     * @return void
     */
    public function test_products_filtered_by_name(): void
    {
        $group = Group::create([
            'name' => 'Group',
            'id_parent' => 0,
        ]);

        Product::create(['name' => 'iPhone 15', 'id_group' => $group->id]);
        Product::create(['name' => 'Samsung Galaxy', 'id_group' => $group->id]);

        foreach (Product::all() as $product) {
            Price::create([
                'id_product' => $product->id,
                'price' => 100.00,
            ]);
        }

        $response = $this->postJson('/api/products', [
            'name' => 'iPhone',
            'page' => 1,
            'per_page' => 12,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('iPhone 15', $data[0]['name']);
    }
}
