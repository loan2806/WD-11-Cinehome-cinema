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

            'quoc_gia_id' => 'required|integer|exists:countries,id',

            'dao_dien' => 'required|string|max:255',

            'dien_vien' => 'required|string',

            'ngon_ngu' => 'required|string|max:255',

            'thoi_luong' => 'required|integer|min:1',

            'gioi_han_tuoi' => 'required|string|max:20',

            'mo_ta' => 'required|string|min:10',

            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'trailer' => [
                'required',
                'regex:/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'ten_phim.required' => 'Vui lòng nhập tên phim',

            'genre_ids.required' => 'Vui lòng chọn ít nhất một thể loại',
            'genre_ids.array' => 'Dữ liệu thể loại không hợp lệ',
            'genre_ids.min' => 'Vui lòng chọn ít nhất một thể loại',

            'quoc_gia_id.required' => 'Vui lòng chọn quốc gia',

            'dao_dien.required' => 'Vui lòng nhập đạo diễn',

            'dien_vien.required' => 'Vui lòng nhập diễn viên',

            'ngon_ngu.required' => 'Vui lòng nhập ngôn ngữ',

            'thoi_luong.required' => 'Vui lòng nhập thời lượng phim',

            'gioi_han_tuoi.required' => 'Vui lòng nhập giới hạn tuổi',

            'mo_ta.required' => 'Vui lòng nhập mô tả phim',

            'poster.image' => 'File phải là hình ảnh',
            'poster.mimes' => 'Poster chỉ hỗ trợ jpg, jpeg, png, webp',

            'trailer.required' => 'Vui lòng nhập link trailer',
            'trailer.regex' => 'Trailer phải là link YouTube hợp lệ',
        ];
    }
}
