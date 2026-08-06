<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. Phim mới cập nhật (Thêm trong 30 ngày gần đây)
        $phimMoiCapNhatCount = Phims::where('created_at', '>=', $today->copy()->subDays(30))->count();

        // 2. Số suất chiếu hôm nay
        $suatChieuHomNayCount = SuatChieu::whereDate('thoi_gian_chieu', $today)
            ->where('trang_thai', '!=', 'huy')
            ->count();

        // 3. Doanh thu vé hôm nay & hôm qua (Bảng ve_xem_phims, cột 'tong_tien')
        $trangThaiVeHopLe = ['da_thanh_toan', 'da_in'];

        $doanhThuVeHomNay = DB::table('ve_xem_phims')
            ->whereIn('trang_thai', $trangThaiVeHopLe)
            ->whereDate('created_at', $today)
            ->sum('tong_tien');

        $doanhThuVeHomQua = DB::table('ve_xem_phims')
            ->whereIn('trang_thai', $trangThaiVeHopLe)
            ->whereDate('created_at', $yesterday)
            ->sum('tong_tien');

        // Tỷ lệ tăng trưởng doanh thu so với hôm qua (%)
        $phanTramTangTruong = 0;
        if ($doanhThuVeHomQua > 0) {
            $phanTramTangTruong = round((($doanhThuVeHomNay - $doanhThuVeHomQua) / $doanhThuVeHomQua) * 100);
        } elseif ($doanhThuVeHomNay > 0) {
            $phanTramTangTruong = 100;
        }

        // 4. Số vé đã bán hôm nay
        $veDaBanHomNay = DB::table('ve_xem_phims')
            ->whereIn('trang_thai', $trangThaiVeHopLe)
            ->whereDate('created_at', $today)
            ->count();

        // 5. Lượng khách vào rạp (Vé trạng thái 'da_in')
        $luongKhachHomNay = DB::table('ve_xem_phims')
            ->where('trang_thai', 'da_in')
            ->whereDate('updated_at', $today)
            ->count();

        if ($luongKhachHomNay === 0) {
            $luongKhachHomNay = $veDaBanHomNay;
        }

        // 6. Doanh thu đồ ăn & Combo hôm nay (Bảng food_invoices, cột 'total', status 'paid')
        $doanhThuDoAnHomNay = DB::table('food_invoices')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total');

        // 7. Danh sách 5 phim mới cập nhật (Tự động nhận diện tên quan hệ thể loại trong Model Phims)
        $phimQuery = Phims::query();

        if (method_exists(Phims::class, 'theLoais')) {
            $phimQuery->with('theLoais');
        } elseif (method_exists(Phims::class, 'theLoai')) {
            $phimQuery->with('theLoai');
        } elseif (method_exists(Phims::class, 'genres')) {
            $phimQuery->with('genres');
        }

        $danhSachPhimMoi = $phimQuery->orderBy('id', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'phimMoiCapNhatCount',
            'suatChieuHomNayCount',
            'doanhThuVeHomNay',
            'phanTramTangTruong',
            'veDaBanHomNay',
            'luongKhachHomNay',
            'doanhThuDoAnHomNay',
            'danhSachPhimMoi'
        ));
    }
}