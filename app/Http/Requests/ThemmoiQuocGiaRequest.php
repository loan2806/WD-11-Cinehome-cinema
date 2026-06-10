<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemmoiQuocGiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_quoc_gia' => 'required|string|max:255',
            'ma_quoc_gia'  => 'required|string|max:10|unique:quoc_gias,ma_quoc_gia',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_quoc_gia.required' => 'Tên quốc gia không được để trống',
            'ma_quoc_gia.required'  => 'Mã quốc gia không được để trống',
            'ma_quoc_gia.unique'    => 'Mã quốc gia đã tồn tại',
            'ma_quoc_gia.max'       => 'Mã quốc gia tối đa 10 ký tự',
        ];
    }
}