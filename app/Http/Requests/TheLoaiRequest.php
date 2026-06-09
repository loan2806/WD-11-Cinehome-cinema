<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TheLoaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $theLoaiId = $this->route('the_loai')?->id;
        // trang_thai chỉ required khi update (có the_loai route parameter), nullable khi create
        $trangThaiRule = $theLoaiId ? 'required|boolean' : 'nullable|boolean';

        return [
            'ten_the_loai' => 'required|string|max:255|unique:the_loais,ten_the_loai,' . ($theLoaiId ?? 'NULL'),
            'mo_ta' => 'nullable|string|max:500',
            'trang_thai' => $trangThaiRule,
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ten_the_loai.required' => 'Vui lòng nhập tên thể loại',
            'ten_the_loai.string' => 'Tên thể loại phải là một chuỗi ký tự',
            'ten_the_loai.max' => 'Tên thể loại không được vượt quá 255 ký tự',
            'ten_the_loai.unique' => 'Tên thể loại này đã tồn tại trong hệ thống',
            'mo_ta.string' => 'Mô tả phải là một chuỗi ký tự',
            'mo_ta.max' => 'Mô tả không được vượt quá 500 ký tự',
            'trang_thai.required' => 'Vui lòng chọn trạng thái',
            'trang_thai.boolean' => 'Trạng thái không hợp lệ',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ten_the_loai' => 'Tên thể loại',
            'mo_ta' => 'Mô tả',
            'trang_thai' => 'Trạng thái',
        ];
    }
}
