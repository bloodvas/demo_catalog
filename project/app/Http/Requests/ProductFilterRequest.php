<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'sort'      => 'nullable|in:price,name,created_at',
            'order'     => 'nullable|in:asc,desc',
            'name'      => 'nullable|string|max:255',
            'group_id'  => 'nullable|integer|exists:groups,id',
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ];
    }
}
