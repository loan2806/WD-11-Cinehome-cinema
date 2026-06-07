<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuatChieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phim_id' => [
                'required',
                'exists:phims,id',
            ],
            'rap_chieu_phim_id' => [
                'required',
                'exists:rap_chieu_phims,id',
            ],
            'phong_chieu_id' => [
                'required',
                'exists:phong_chieus,id',
            ],
            'thoi_gian_chieu' => [
                'required',
                'date',
                'after:now',
            ],
            'gia_ve' => [
                'required',
                'numeric',
                'min:0',
            ],
            'trang_thai' => [
                'required',
                'string',
                'in:' . implode(',', array_keys(\App\Models\SuatChieu::TRANG_THAI_LIST)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phim_id.required' => 'Phim không được để trống.',
            'phong_chieu_id.required' => 'Phòng chiếu không được để trống.',
            'thoi_gian_chieu.required' => 'Thời gian chiếu không được để trống.',
            'thoi_gian_chieu.after' => 'Thời gian chiếu phải là ngày trong tương lai.',
            'gia_ve.required' => 'Giá vé không được để trống.',
            'trang_thai.required' => 'Trạng thái không được để trống.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->kiemTraChongLan($validator);
        });
    }

    protected function kiemTraChongLan($validator): void
    {
        $phim = \App\Models\Phims::find($this->phim_id);
        if (!$phim || !$phim->thoi_luong) {
            return;
        }

        $thoiGianChieu = \Carbon\Carbon::parse($this->thoi_gian_chieu);
        $thoiGianKetThuc = $thoiGianChieu->copy()->addMinutes($phim->thoi_luong);

        $suatChieuChongLan = \App\Models\SuatChieu::where('phong_chieu_id', $this->phong_chieu_id)
            ->where(function ($query) use ($thoiGianChieu, $thoiGianKetThuc) {
                $query->where(function ($q) use ($thoiGianChieu, $thoiGianKetThuc) {
                    $q->where('thoi_gian_chieu', '<', $thoiGianKetThuc)
                      ->where('thoi_gian_ket_thuc', '>', $thoiGianChieu);
                });
            })
            ->when($this->route('suat_chieu'), function ($query, $suatChieu) {
                $query->where('id', '!=', $suatChieu->id);
            })
            ->exists();

        if ($suatChieuChongLan) {
            $validator->errors()->add(
                'thoi_gian_chieu',
                'Suất chiếu bị chồng lấn với suất chiếu đã có trong phòng này.'
            );
        }
    }
}
