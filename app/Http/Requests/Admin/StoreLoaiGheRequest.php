<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoaiGheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_loai' => [
                'required',
                'string',
                'max:50',
                'unique:loai_ghes,ten_loai',
            ],
            'mo_ta' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phu_thu' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'mau_sac' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_loai.required' => 'Tên loại ghế không được để trống.',
            'ten_loai.unique' => 'Tên loại ghế đã tồn tại.',
            'phu_thu.required' => 'Phụ thu không được để trống.',
            'phu_thu.numeric' => 'Phụ thu phải là số.',
            'phu_thu.min' => 'Phụ thu không được nhỏ hơn 0.',
            'mau_sac.regex' => 'Màu sắc phải là mã hex (VD: #FF5733).',
        ];
    }
}
