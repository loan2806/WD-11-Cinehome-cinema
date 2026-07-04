<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemmoiFoodVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'value' => trim($this->value ?? ''),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
    public function rules(): array
    {
        return [
            'value' => [
                'required',
                'string',
                'max:50',
                Rule::unique('food_variants', 'value')
                    ->where(function ($query) {
                        return $query->where(
                            'food_id',
                            $this->route('food')->id
                        );
                    }),
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Vui lòng nhập tên biến thể.',
            'value.max' => 'Tên biến thể không được vượt quá 50 ký tự.',
            'value.unique' => 'Biến thể này đã tồn tại.',

            'price.required' => 'Vui lòng nhập giá.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0.',

            'stock_quantity.required' => 'Vui lòng nhập tồn kho.',
            'stock_quantity.integer' => 'Tồn kho phải là số nguyên.',
            'stock_quantity.min' => 'Tồn kho không được âm.',

            'is_active.boolean' => 'Trạng thái biến thể không hợp lệ.',
        ];
    }
}
