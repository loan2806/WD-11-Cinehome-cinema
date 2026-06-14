<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHangGheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hangGheId = $this->route('hang_ghe')?->id ?? $this->route('hang_ghe');

        return [
            'ten_hang' => [
                'required',
                'string',
                'max:10',
                Rule::unique('hang_ghes')
                    ->where('phong_chieu_id', $this->phong_chieu_id)
                    ->ignore($hangGheId),
            ],
            'la_hang_couple' => ['nullable', 'boolean'],
            'loai_ghe_mac_dinh_id' => ['nullable', 'exists:loai_ghes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_hang.required' => 'Tên hàng ghế không được để trống.',
            'ten_hang.unique' => 'Tên hàng ghế đã tồn tại trong phòng chiếu này.',
            'loai_ghe_mac_dinh_id.exists' => 'Loại ghế mặc định không hợp lệ.',
        ];
    }
}
