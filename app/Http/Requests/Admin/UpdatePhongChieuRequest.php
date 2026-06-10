<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePhongChieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phongChieuId = $this->route('phong_chieu')?->id ?? $this->route('phong_chieu');

        return [
            'rap_chieu_phim_id' => [
                'required',
                'exists:rap_chieu_phims,id',
            ],
            'ten_phong' => [
                'required',
                'string',
                'max:50',
                Rule::unique('phong_chieus')
                    ->where('rap_chieu_phim_id', $this->rap_chieu_phim_id)
                    ->whereNull('deleted_at')
                    ->ignore($phongChieuId),
            ],
            'loai_phong' => [
                'required',
                Rule::in(['2d', '3d', 'imax', '4dx']),
            ],
            'suc_chua' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],
            'trang_thai' => [
                'required',
                Rule::in(['hoat_dong', 'bao_tri', 'ngung_hoat_dong']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ten_phong.required' => 'Tên phòng chiếu không được để trống.',
            'ten_phong.unique' => 'Tên phòng chiếu đã tồn tại trong rạp này.',
            'suc_chua.required' => 'Sức chứa không được để trống.',
            'suc_chua.min' => 'Sức chứa phải lớn hơn 0.',
            'loai_phong.required' => 'Loại phòng chiếu không được để trống.',
            'trang_thai.required' => 'Trạng thái không được để trống.',
        ];
    }
}
