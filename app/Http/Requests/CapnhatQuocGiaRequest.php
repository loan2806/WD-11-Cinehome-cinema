<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CapnhatQuocGiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $quocGia = $this->route('quoc_gia'); // đúng theo route model binding

        return [
            'ten_quoc_gia' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quoc_gias', 'ten_quoc_gia')->ignore($quocGia->id),
            ],

            'ma_quoc_gia' => [
                'required',
                'string',
                'max:10',
                Rule::unique('quoc_gias', 'ma_quoc_gia')->ignore($quocGia->id),
            ],

            'trang_thai' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_quoc_gia.required' => 'Vui lòng nhập tên quốc gia.',
            'ten_quoc_gia.unique' => 'Tên quốc gia đã tồn tại.',

            'ma_quoc_gia.required' => 'Vui lòng nhập mã quốc gia.',
            'ma_quoc_gia.unique' => 'Mã quốc gia đã tồn tại.',

            'trang_thai.boolean' => 'Trạng thái không hợp lệ.',
        ];
    }
}