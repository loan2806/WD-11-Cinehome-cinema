<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_phim' => 'required|string|max:255',
            'genre_ids' => 'required|array|min:1',
            'genre_ids.*' => 'required|integer|exists:genres,id',
            'thoi_luong' => 'required|integer|min:1',
            'quoc_gia_id' => 'required|integer|exists:countries,id',
            'ngay_khoi_chieu' => 'required|date',
            'mo_ta' => 'required|string|min:10',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer' => [
                'required',
                'regex:/^(https?:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_phim.required' => 'Tên phim không được để trống',
            'genre_ids.required' => 'Vui lòng chọn ít nhất một thể loại',
            'genre_ids.*.exists' => 'Thể loại được chọn không hợp lệ',
            'thoi_luong.required' => 'Thời lượng phim là bắt buộc',
            'quoc_gia_id.required' => 'Quốc gia là bắt buộc',
            'quoc_gia_id.exists' => 'Quốc gia được chọn không hợp lệ',
            'mo_ta.required' => 'Mô tả phim là bắt buộc',
            'trailer.required' => 'Link trailer là bắt buộc',
        ];
    }
}