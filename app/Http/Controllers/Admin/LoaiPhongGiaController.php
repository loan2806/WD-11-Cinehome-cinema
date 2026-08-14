<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaiDatHeThong;
use App\Models\PhongChieu;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class LoaiPhongGiaController extends Controller
{
    use Loggable;

    /**
     * Danh sách phụ thu vé theo TỪNG LOẠI phòng chiếu (2D/3D/IMAX/4DX) — mỗi
     * loại một dòng duy nhất, áp dụng đồng loạt cho MỌI phòng cùng loại đó.
     * Thêm phòng mới ở "Quản lý phòng chiếu" với loại nào thì phòng đó tự
     * nhận đúng mức phụ thu đang cấu hình cho loại đó, không cần chỉnh riêng.
     */
    public function index()
    {
        $soPhongTheoLoai = PhongChieu::selectRaw('loai_phong, COUNT(*) as so_phong')
            ->groupBy('loai_phong')
            ->pluck('so_phong', 'loai_phong');

        $danhSach = collect(PhongChieu::LOAI_PHONG)->map(function ($ten, $ma) use ($soPhongTheoLoai) {
            return [
                'ma' => $ma,
                'ten' => $ten,
                'phu_thu' => PhongChieu::phuThuTheoLoai($ma),
                'so_phong' => $soPhongTheoLoai[$ma] ?? 0,
            ];
        })->values();

        $settings = CaiDatHeThong::first();
        $giaNgayThuong = $settings->gia_ngay_thuong ?? 75000;
        $giaCuoiTuan = $settings->gia_cuoi_tuan ?? 120000;

        return view('admin.loai-phong-gia.index', compact('danhSach', 'giaNgayThuong', 'giaCuoiTuan'));
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

        foreach ($data['phu_thu'] as $loaiPhong => $value) {
            if (! array_key_exists($loaiPhong, PhongChieu::LOAI_PHONG)) {
                continue;
            }

            PhongChieu::where('loai_phong', $loaiPhong)->update(['phu_thu' => $value]);
        }

        $this->ghiNhatKy(
            $request,
            'Cập nhật giá theo loại phòng chiếu',
            'Cơ sở vật chất phòng',
            'Cập nhật phụ thu vé theo từng loại phòng chiếu'
        );

        return back()->with('success', 'Đã cập nhật giá theo loại phòng chiếu.');
    }
}
