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

            /*
            |--------------------------------------------------------------------------
            | TIÊU ĐỀ
            |--------------------------------------------------------------------------
            */
            'tieu_de' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | NỘI DUNG
            |--------------------------------------------------------------------------
            */
            'noi_dung' => [
                'required',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | LOẠI
            |--------------------------------------------------------------------------
            */
            'loai' => [
                'required',
                Rule::in([
                    'system',
                    'promo',
                    'warning',
                    'info',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | ĐỐI TƯỢNG NHẬN
            |--------------------------------------------------------------------------
            */
            'doi_tuong_nhan' => [
                'required',
                Rule::in([
                    'all',
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                    'hang_thanh_vien',
                    'nguoi_dung_cu_the',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | HẠNG THÀNH VIÊN
            |--------------------------------------------------------------------------
            */
            'hang_thanh_vien' => [
                'nullable',
                'required_if:doi_tuong_nhan,hang_thanh_vien',
                Rule::in([
                    'member',
                    'silver',
                    'gold',
                    'platinum',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | NGƯỜI DÙNG CỤ THỂ
            |--------------------------------------------------------------------------
            |
            | Đã đổi từ:
            |
            | nguoi_dung_cu_the = integer
            |
            | thành:
            |
            | nguoi_dung_cu_the = array
            | nguoi_dung_cu_the.* = integer
            |
            */
            'nguoi_dung_cu_the' => [
                'nullable',
                'array',
                'required_if:doi_tuong_nhan,nguoi_dung_cu_the',
                'min:1',
            ],

            'nguoi_dung_cu_the.*' => [
                'integer',
                'distinct',
                Rule::exists('nguoi_dungs', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'tieu_de.required' =>
                'Tiêu đề thông báo không được để trống.',

            'tieu_de.string' =>
                'Tiêu đề thông báo không hợp lệ.',

            'tieu_de.max' =>
                'Tiêu đề thông báo không được vượt quá 255 ký tự.',


            'noi_dung.required' =>
                'Nội dung thông báo không được để trống.',

            'noi_dung.string' =>
                'Nội dung thông báo không hợp lệ.',

            'noi_dung.max' =>
                'Nội dung thông báo không được vượt quá 1000 ký tự.',


            'loai.required' =>
                'Vui lòng chọn loại thông báo.',

            'loai.in' =>
                'Loại thông báo không hợp lệ.',


            'doi_tuong_nhan.required' =>
                'Vui lòng chọn đối tượng nhận.',

            'doi_tuong_nhan.in' =>
                'Đối tượng nhận không hợp lệ.',


            'hang_thanh_vien.required_if' =>
                'Vui lòng chọn hạng thành viên.',

            'hang_thanh_vien.in' =>
                'Hạng thành viên không hợp lệ.',


            'nguoi_dung_cu_the.required_if' =>
                'Vui lòng chọn ít nhất một người dùng.',

            'nguoi_dung_cu_the.array' =>
                'Danh sách người nhận không hợp lệ.',

            'nguoi_dung_cu_the.min' =>
                'Vui lòng chọn ít nhất một người dùng.',

            'nguoi_dung_cu_the.*.integer' =>
                'Người dùng được chọn không hợp lệ.',

            'nguoi_dung_cu_the.*.distinct' =>
                'Danh sách người nhận không được trùng nhau.',

            'nguoi_dung_cu_the.*.exists' =>
                'Một trong những người dùng được chọn không tồn tại.',
        ];
    }
}