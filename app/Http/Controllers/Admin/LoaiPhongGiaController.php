<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhongChieu;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class LoaiPhongGiaController extends Controller
{
    use Loggable;

    /**
     * Danh sách phụ thu vé theo TỪNG PHÒNG CHIẾU cụ thể — luôn khớp với danh
     * sách phòng thật ở "Quản lý phòng chiếu". Thêm phòng mới ở đó là tự động
     * có dòng để chỉnh giá ở đây, không cần đồng bộ thủ công.
     */
    public function index()
    {
        $danhSach = PhongChieu::with('rapChieuPhim')
            ->orderBy('rap_chieu_phim_id')
            ->orderBy('ten_phong')
            ->get();

        return view('admin.loai-phong-gia.index', compact('danhSach'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'phu_thu' => ['required', 'array'],
            'phu_thu.*' => ['required', 'numeric', 'min:0'],
        ], [
            'phu_thu.*.required' => 'Vui lòng nhập phụ thu.',
            'phu_thu.*.numeric' => 'Phụ thu phải là số.',
            'phu_thu.*.min' => 'Phụ thu không được âm.',
        ]);

        foreach ($data['phu_thu'] as $id => $value) {
            PhongChieu::whereKey($id)->update(['phu_thu' => $value]);
        }

        $this->ghiNhatKy(
            $request,
            'Cập nhật giá theo phòng chiếu',
            'Cơ sở vật chất phòng',
            'Cập nhật phụ thu vé theo từng phòng chiếu'
        );

        return back()->with('success', 'Đã cập nhật giá theo phòng chiếu.');
    }
}
