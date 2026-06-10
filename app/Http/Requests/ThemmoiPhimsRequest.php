<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemmoiPhimsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | BASIC
            |--------------------------------------------------------------------------
            */

            'ten_phim' => 'required|string|max:255',

            'the_loai_id' => 'required|array|min:1',
            'the_loai_id.*' => 'required|integer|exists:the_loais,id',

            'quoc_gia_id' => 'required|integer|exists:quoc_gias,id',

            'dao_dien' => 'required|string|max:255',

            'dien_vien' => 'required|string',

            'ngon_ngu' => 'required|string|max:255',

            'thoi_luong' => 'required|integer|min:1',

            'gioi_han_tuoi' => 'required|string|max:20',

            'mo_ta' => 'required|string|min:10',

            /*
            |--------------------------------------------------------------------------
            | POSTER
            |--------------------------------------------------------------------------
            */

            'poster' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            /*
            |--------------------------------------------------------------------------
            | TRAILER
            |--------------------------------------------------------------------------
            */

            'trailer' => [
                'required',
                'regex:/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/'
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | TÊN PHIM
            |--------------------------------------------------------------------------
            */

            'ten_phim.required' => 'Vui lòng nhập tên phim',

            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự',

            /*
            |--------------------------------------------------------------------------
            | THỂ LOẠI
            |--------------------------------------------------------------------------
            */

            'the_loai_id.required' => 'Vui lòng chọn ít nhất một thể loại',

            'the_loai_id.array' => 'Dữ liệu thể loại không hợp lệ',

            'the_loai_id.min' => 'Vui lòng chọn ít nhất một thể loại',

            'the_loai_id.*.exists' => 'Thể loại được chọn không tồn tại',

            /*
            |--------------------------------------------------------------------------
            | QUỐC GIA
            |--------------------------------------------------------------------------
            */

            'quoc_gia_id.required' => 'Vui lòng chọn quốc gia',

            'quoc_gia_id.exists' => 'Quốc gia không tồn tại',

            /*
            |--------------------------------------------------------------------------
            | ĐẠO DIỄN
            |--------------------------------------------------------------------------
            */

            'dao_dien.required' => 'Vui lòng nhập đạo diễn',

            /*
            |--------------------------------------------------------------------------
            | DIỄN VIÊN
            |--------------------------------------------------------------------------
            */

            'dien_vien.required' => 'Vui lòng nhập diễn viên',

            /*
            |--------------------------------------------------------------------------
            | NGÔN NGỮ
            |--------------------------------------------------------------------------
            */

            'ngon_ngu.required' => 'Vui lòng nhập ngôn ngữ',

            /*
            |--------------------------------------------------------------------------
            | THỜI LƯỢNG
            |--------------------------------------------------------------------------
            */

            'thoi_luong.required' => 'Vui lòng nhập thời lượng phim',

            'thoi_luong.integer' => 'Thời lượng phải là số',

            'thoi_luong.min' => 'Thời lượng phải lớn hơn 0',

            /*
            |--------------------------------------------------------------------------
            | GIỚI HẠN TUỔI
            |--------------------------------------------------------------------------
            */

            'gioi_han_tuoi.required' => 'Vui lòng nhập giới hạn tuổi',

            /*
            |--------------------------------------------------------------------------
            | MÔ TẢ
            |--------------------------------------------------------------------------
            */

            'mo_ta.required' => 'Vui lòng nhập mô tả phim',

            'mo_ta.min' => 'Mô tả phải tối thiểu 10 ký tự',

            /*
            |--------------------------------------------------------------------------
            | POSTER
            |--------------------------------------------------------------------------
            */

            'poster.required' => 'Vui lòng chọn poster phim',

            'poster.image' => 'File phải là hình ảnh',

            'poster.mimes' => 'Poster chỉ hỗ trợ jpg, jpeg, png, webp',

            'poster.max' => 'Poster không được vượt quá 2MB',

            /*
            |--------------------------------------------------------------------------
            | TRAILER
            |--------------------------------------------------------------------------
            */

            'trailer.required' => 'Vui lòng nhập link trailer',

            'trailer.regex' => 'Trailer phải là link YouTube hợp lệ',
        ];
    }
}