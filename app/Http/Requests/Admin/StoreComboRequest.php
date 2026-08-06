<?php

namespace App\Http\Requests\Admin;

use App\Models\DanhMucDoAn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => filled($this->sku)
                ? strtoupper(trim($this->sku))
                : null,
            'name' => trim($this->name),
            'description' => filled($this->description)
                ? trim($this->description)
                : null,
            'sort_order' => (int) ($this->sort_order ?? 0),
            'price' => filled($this->price)
                ? (float) $this->price
                : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/i',
                Rule::unique('foods', 'sku'),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'category_id' => [
                'required',
                'exists:food_categories,id',
                function ($attribute, $value, $fail) {
                    $category = DanhMucDoAn::find($value);
                    if (! $category || ! $category->is_combo) {
                        $fail('Danh mục phải là Combo.');
                    }
                },
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'combo_items' => [
                'required',
                'array',
                'min:2',
            ],
            'combo_items.*.variant_id' => [
                'required',
                'distinct',
                'exists:food_variants,id',
            ],
            'combo_items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'sort_order' => [
                'nullable',
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
            'sku.unique' => 'SKU đã tồn tại.',
            'sku.regex' => 'SKU chỉ gồm chữ, số, dấu - hoặc _.',
            'sku.max' => 'SKU không được vượt quá 50 ký tự.',
            'name.required' => 'Vui lòng nhập tên combo.',
            'name.max' => 'Tên combo không được vượt quá 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục combo.',
            'price.required' => 'Vui lòng nhập giá combo.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0.',
            'combo_items.required' => 'Vui lòng thêm ít nhất 2 món cho combo.',
            'combo_items.array' => 'Danh sách thành phần combo không hợp lệ.',
            'combo_items.min' => 'Combo phải có ít nhất 2 món.',
            'combo_items.*.variant_id.required' => 'Vui lòng chọn biến thể món.',
            'combo_items.*.variant_id.exists' => 'Biến thể được chọn không tồn tại.',
            'combo_items.*.quantity.required' => 'Vui lòng nhập số lượng thành phần.',
            'combo_items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'combo_items.*.quantity.min' => 'Số lượng thành phần phải lớn hơn 0.',
            'combo_items.*.variant_id.distinct' => 'Mỗi biến thể combo chỉ được chọn một lần.',
            'image.required' => 'Vui lòng chọn ảnh combo.',
            'image.image' => 'Vui lòng chọn đúng định dạng ảnh.',
            'image.mimes' => 'Ảnh chỉ được phép có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Kích thước ảnh không được vượt quá 2MB.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'sort_order.integer' => 'Thứ tự phải là số.',
            'sort_order.min' => 'Thứ tự phải lớn hơn hoặc bằng 0.',
            'is_active.boolean' => 'Trạng thái hiển thị không hợp lệ.',
        ];
    }
}
