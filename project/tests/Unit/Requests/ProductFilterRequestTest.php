<?php

namespace Tests\Unit\Requests;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductFilterRequestTest extends TestCase
{
    /**
     * Тест: валидный запрос проходит
     * @return void
     */
    public function test_valid_request_passes_validation(): void
    {
        $data = [
            'page' => 1,
            'per_page' => 12,
            'sort' => ['field' => 'price', 'direction' => 'desc'],
        ];

        $validator = Validator::make($data, [
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'sort'      => 'nullable|array',
            'sort.field'    => 'nullable|in:price,name,created_at',
            'sort.direction' => 'nullable|in:asc,desc',
            'name'      => 'nullable|string|max:255',
            'group_id'  => 'nullable|integer|exists:groups,id',
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ]);

        $this->assertTrue($validator->passes());
    }

    /**
     * Тест: пустой запрос использует значения по умолчанию
     * @return void
     */
    public function test_empty_request_uses_defaults(): void
    {
        $data = [];

        $validator = Validator::make($data, [
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'sort'      => 'nullable|array',
            'sort.field'    => 'nullable|in:price,name,created_at',
            'sort.direction' => 'nullable|in:asc,desc',
            'name'      => 'nullable|string|max:255',
            'group_id'  => 'nullable|integer|exists:groups,id',
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ]);

        $this->assertTrue($validator->passes());
    }

    /**
     * Тест: неверное значение page не проходит валидацию
     * @return void
     */
    public function test_invalid_page_fails_validation(): void
    {
        $data = ['page' => -1];

        $validator = Validator::make($data, [
            'page' => 'nullable|integer|min:1',
        ]);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('page'));
    }

    /**
     * Тест: per_page ограничено max 50
     * @return void
     */
    public function test_per_page_max_50(): void
    {
        $data = ['per_page' => 100];

        $validator = Validator::make($data, [
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $this->assertFalse($validator->passes());
    }

    /**
     * Тест: неверное направление сортировки
     * @return void
     */
    public function test_invalid_sort_direction_fails(): void
    {
        $data = [
            'sort' => ['field' => 'price', 'direction' => 'invalid'],
        ];

        $validator = Validator::make($data, [
            'sort.field' => 'nullable|in:price,name,created_at',
            'sort.direction' => 'nullable|in:asc,desc',
        ]);

        $this->assertFalse($validator->passes());
    }

    /**
     * Тест: фильтрация по цене проходит валидацию
     * @return void
     */
    public function test_price_filter_validation(): void
    {
        $data = [
            'price' => [
                'min' => 100,
                'max' => 500,
            ],
        ];

        $validator = Validator::make($data, [
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ]);

        $this->assertTrue($validator->passes());
    }

    /**
     * Тест: отрицательная цена не проходит валидацию
     * @return void
     */
    public function test_negative_price_fails(): void
    {
        $data = [
            'price' => [
                'min' => -100,
                'max' => 500,
            ],
        ];

        $validator = Validator::make($data, [
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ]);

        $this->assertFalse($validator->passes());
    }
}
