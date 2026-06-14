<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGheNgoiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phong_chieu_id' => [
                'required',
                'exists:phong_chieus,id',
            ],
            'hang_ghe_id' => [
                'required',
                'exists:hang_ghes,id',
            ],
            'loai_ghe_id' => [
                'required',
                'exists:loai_ghes,id',
            ],
            'ma_ghe' => [
                'required',
                'string',
                'max:10',
                Rule::unique('ghe_ngois', 'ma_ghe')->where('phong_chieu_id', $this->phong_chieu_id),
            ],
            'cot' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('ghe_ngois', 'cot')->where('hang_ghe_id', $this->hang_ghe_id),
            ],
            'couple_group_id' => [
                'nullable',
                'string',
                'max:50',
            ],
            'trang_thai' => [
                'required',
                'in:hoat_dong,bao_tri',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ma_ghe.required' => 'Mã ghế không được để trống.',
            'ma_ghe.unique' => 'Mã ghế đã tồn tại trong phòng chiếu này.',
            'phong_chieu_id.required' => 'Phòng chiếu không được để trống.',
            'hang_ghe_id.required' => 'Hàng ghế không được để trống.',
            'loai_ghe_id.required' => 'Loại ghế không được để trống.',
            'cot.unique' => 'Cột này đã có ghế trong hàng được chọn.',
        ];
    }
}
