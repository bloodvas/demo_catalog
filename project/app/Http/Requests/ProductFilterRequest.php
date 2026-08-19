<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 12),
            'sort.field'  => $this->input('sort.field', 'price'),
            'sort.direction' => $this->input('sort.direction', 'desc'),
        ]);
    }

    public function rules(): array
    {
        return [
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'sort'      => 'nullable|array',          // <-- теперь массив
            'sort.field'    => 'nullable|in:price,name,created_at',
            'sort.direction' => 'nullable|in:asc,desc',
            'name'      => 'nullable|string|max:255',
            'group_id'  => 'nullable|integer|exists:groups,id',
            'price'     => 'nullable|array',
            'price.min' => 'nullable|numeric|min:0',
            'price.max' => 'nullable|numeric|min:0',
        ];
    }
}
