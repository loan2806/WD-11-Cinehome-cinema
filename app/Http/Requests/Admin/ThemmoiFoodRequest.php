<?php

namespace App\Http\Requests\Admin;

use App\Models\DanhMucDoAn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemmoiFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    protected function prepareForValidation(): void
    {
        $data = [

            'sku' => filled($this->sku)
                ? strtoupper(trim($this->sku))
                : null,

            'name' => trim($this->name),

            'category_id' => $this->category_id,

            'description' => filled($this->description)
                ? trim($this->description)
                : null,

            'sort_order' => (int) ($this->sort_order ?? 0),

            'price' => filled($this->price)
                ? (float) $this->price
                : null,

            'is_active' => $this->boolean('is_active'),
        ];


        // Nếu là combo thì bỏ hoàn toàn variants
        if ($this->isComboCategory()) {
            $data['variants'] = [];
        }


        $this->merge($data);
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
                'exists:food_categories,id'
            ],



            // Giá chỉ bắt buộc khi Combo
            'price' => $this->isComboCategory()
                ? [
                    'required',
                    'numeric',
                    'min:0'
                ]
                : [
                    'nullable',
                    'numeric',
                    'min:0'
                ],



            // Thành phần combo
            'combo_items' => $this->isComboCategory()
                ? [
                    'required',
                    'array',
                    'min:2'
                ]
                : [
                    'nullable',
                    'array'
                ],


            'combo_items.*.variant_id' => $this->isComboCategory()
                ? [
                    'required',
                    'exists:food_variants,id'
                ]
                : [
                    'nullable'
                ],


            'combo_items.*.quantity' => $this->isComboCategory()
                ? [
                    'required',
                    'integer',
                    'min:1'
                ]
                : [
                    'nullable'
                ],



            // Biến thể món thường
            'variants' => $this->isComboCategory()
                ? [
                    'nullable',
                    'array'
                ]
                : [
                    'required',
                    'array',
                    'min:1'
                ],


            'variants.*.value' => $this->isComboCategory()
                ? [
                    'nullable',
                    'string',
                    'max:50'
                ]
                : [
                    'required',
                    'string',
                    'max:50'
                ],


            'variants.*.price' => $this->isComboCategory()
                ? [
                    'nullable',
                    'numeric',
                    'min:0'
                ]
                : [
                    'required',
                    'numeric',
                    'min:0'
                ],


            'variants.*.stock_quantity' => $this->isComboCategory()
                ? [
                    'nullable',
                    'integer',
                    'min:0'
                ]
                : [
                    'required',
                    'integer',
                    'min:0'
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


            'name.required' => 'Vui lòng nhập tên món.',
            'name.max' => 'Tên món không được vượt quá 255 ký tự.',


            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không tồn tại.',



            'price.required' => 'Vui lòng nhập giá combo.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0.',



            'combo_items.required' => 'Vui lòng thêm ít nhất một thành phần cho combo.',
            'combo_items.array' => 'Danh sách thành phần không hợp lệ.',
            'combo_items.min' => 'Combo phải có ít nhất 2 món.',


            'combo_items.*.variant_id.required' => 'Vui lòng chọn biến thể.',
            'combo_items.*.variant_id.exists' => 'Biến thể được chọn không tồn tại.',


            'combo_items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'combo_items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'combo_items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',



            'variants.required' => 'Vui lòng thêm ít nhất 1 biến thể.',
            'variants.array' => 'Dữ liệu biến thể không hợp lệ.',
            'variants.min' => 'Vui lòng thêm ít nhất 1 biến thể.',


            'variants.*.value.required' => 'Vui lòng nhập tên biến thể.',
            'variants.*.value.max' => 'Tên biến thể không được quá 50 ký tự.',


            'variants.*.price.required' => 'Vui lòng nhập giá cho biến thể.',
            'variants.*.price.numeric' => 'Giá phải là số.',
            'variants.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0.',


            'variants.*.stock_quantity.required' => 'Vui lòng nhập tồn kho cho biến thể.',
            'variants.*.stock_quantity.integer' => 'Tồn kho phải là số nguyên.',
            'variants.*.stock_quantity.min' => 'Tồn kho không được âm.',



            'image.required' => 'Vui lòng chọn ảnh sản phẩm.',
            'image.image' => 'Vui lòng chọn đúng định dạng ảnh.',
            'image.mimes' => 'Ảnh chỉ được phép có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Kích thước ảnh không được vượt quá 2MB.',



            'description.string' => 'Mô tả không hợp lệ.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',



            'sort_order.integer' => 'Thứ tự phải là số.',
            'sort_order.min' => 'Thứ tự phải lớn hơn hoặc bằng 0.',


            'is_active.boolean' => 'Trạng thái hiển thị không hợp lệ.',

        ];
    }



    protected function isComboCategory(): bool
    {
        $category = DanhMucDoAn::find(
            $this->input('category_id')
        );


        return $category?->is_combo ?? false;
    }
}