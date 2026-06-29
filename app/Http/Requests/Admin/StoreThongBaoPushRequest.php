<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThongBaoPushRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tieu_de' => ['required', 'string', 'max:255'],
            'noi_dung' => ['required', 'string'],
            'loai' => ['required', Rule::in(['info', 'success', 'warning', 'danger'])],
            'doi_tuong_nhan' => ['required', Rule::in(['all', 'khach_hang', 'nhan_vien', 'quan_tri_vien', 'nguoi_dung_cu_the'])],
            'nguoi_dung_cu_the' => ['nullable', 'integer', 'exists:nguoi_dungs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Tiêu đề thông báo không được để trống.',
            'tieu_de.max' => 'Tiêu đề thông báo không được vượt quá 255 ký tự.',
            'noi_dung.required' => 'Nội dung thông báo không được để trống.',
            'loai.required' => 'Loại thông báo không được để trống.',
            'loai.in' => 'Loại thông báo không hợp lệ.',
            'doi_tuong_nhan.required' => 'Đối tượng nhận không được để trống.',
            'doi_tuong_nhan.in' => 'Đối tượng nhận không hợp lệ.',
            'nguoi_dung_cu_the.exists' => 'Người dùng được chọn không tồn tại.',
        ];
    }
}
