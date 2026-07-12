<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BangLuong;
use App\Models\ChamCong;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BangLuongController extends Controller
{
    private function calculateProvisionalSalary($employee, $thang, $nam)
    {
        $chamCongs = ChamCong::where('nguoi_dung_id', $employee->id)
            ->whereMonth('ngay', $thang)
            ->whereYear('ngay', $nam)
            ->get();

        $tongNgayCong = $chamCongs->where('nghi_phep', false)
                                   ->where('nghi_khong_phep', false)
                                   ->whereNotNull('gio_ra')
                                   ->count();
        $tongGioLam = $chamCongs->sum('so_gio_lam');
        $tongGioTangCa = $chamCongs->sum('so_gio_tang_ca');
        $soLanDiMuon = $chamCongs->where('di_muon', true)->count();
        $soLanVeSom = $chamCongs->where('ve_som', true)->count();
        $soNgayNghiPhep = $chamCongs->where('nghi_phep', true)->count();
        $soNgayNghiKhongPhep = $chamCongs->where('nghi_khong_phep', true)->count();

        $luongCoBan = $employee->luong_co_ban;
        
        // Quy chuẩn: 26 ngày công chuẩn
        $luongNgay = $luongCoBan / 26;
        $luongGio = $luongNgay / 8;

        $luongThoiGian = $luongNgay * $tongNgayCong;
        $luongTangCa = $tongGioTangCa * $luongGio * 1.5;

        // Tự động tính phạt
        $phatDiMuon = $soLanDiMuon * 50000; // 50k/lần
        $phatVeSom = $soLanVeSom * 50000;   // 50k/lần
        $phatKhongPhep = $soNgayNghiKhongPhep * 200000; // 200k/ngày nghỉ không phép
        $phatTuDong = $phatDiMuon + $phatVeSom + $phatKhongPhep;

        $luongThucNhanTamTinh = max(0, $luongThoiGian + $luongTangCa - $phatTuDong);

        return (object)[
            'id' => null, // Không có id vì chưa lưu
            'nguoi_dung_id' => $employee->id,
            'nguoiDung' => $employee,
            'thang' => $thang,
            'nam' => $nam,
            'tong_ngay_cong' => $tongNgayCong,
            'tong_gio_lam' => $tongGioLam,
            'tong_gio_tang_ca' => $tongGioTangCa,
            'so_lan_di_muon' => $soLanDiMuon,
            'so_lan_ve_som' => $soLanVeSom,
            'so_ngay_nghi_phep' => $soNgayNghiPhep,
            'so_ngay_nghi_khong_phep' => $soNgayNghiKhongPhep,
            'luong_co_ban' => $luongCoBan,
            'phu_cap' => 0,
            'thuong' => 0,
            'phat' => $phatTuDong,
            'luong_thuc_nhan' => round($luongThucNhanTamTinh, 2),
            'trang_thai' => 'tam_tinh',
            'is_tam_tinh' => true,
            // Thêm các thuộc tính cho chi tiết tính lương
            'luong_thoi_gian' => round($luongThoiGian, 2),
            'luong_tang_ca' => round($luongTangCa, 2),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $loaiLoc = $request->input('loai_loc', 'thang'); // thang, quy, nam
        $thang = $request->input('thang', date('m'));
        $quy = $request->input('quy', ceil(date('m') / 3));
        $nam = $request->input('nam', date('Y'));

        // Xác định mảng các tháng cần lấy
        $months = [];
        if ($loaiLoc == 'nam') {
            $months = range(1, 12);
        } elseif ($loaiLoc == 'quy') {
            $months = [($quy - 1) * 3 + 1, ($quy - 1) * 3 + 2, ($quy - 1) * 3 + 3];
        } else {
            $months = [$thang];
        }

        // Lấy danh sách nhân viên thuộc chi nhánh để làm bộ lọc
        $nvQuery = NguoiDung::where('vai_tro', 'nhan_vien');
        if ($user->rap_chieu_phim_id !== null) {
            $nvQuery->where('rap_chieu_phim_id', $user->rap_chieu_phim_id);
        }
        $nhanViens = $nvQuery->get();

        // Lọc nhân viên cho danh sách hiển thị
        $listQuery = clone $nvQuery;
        if ($request->filled('nhan_vien_id')) {
            $listQuery->where('id', $request->nhan_vien_id);
        }

        $employeesPaginator = $listQuery->paginate(10);
        
        // Map data: Mỗi nhân viên sẽ chứa mảng chi tiết các tháng, và tổng hợp số liệu
        $employeesPaginator->getCollection()->transform(function ($emp) use ($months, $nam) {
            $monthlyData = collect();
            $tongThucNhan = 0;
            $tongNgayCong = 0;
            $tongGioTangCa = 0;
            $tongPhat = 0;
            $soThangDaChot = 0;

            foreach ($months as $m) {
                // Thử lấy lương đã chốt
                $bl = BangLuong::with('nguoiDung')->where('nguoi_dung_id', $emp->id)->where('thang', $m)->where('nam', $nam)->first();
                if ($bl) {
                    $bl->is_tam_tinh = false;
                    $monthlyData->push($bl);
                    
                    $tongThucNhan += $bl->luong_thuc_nhan;
                    $tongNgayCong += $bl->tong_ngay_cong;
                    $tongGioTangCa += $bl->tong_gio_tang_ca;
                    $tongPhat += $bl->phat;
                    $soThangDaChot++;
                } else {
                    // Nếu chưa chốt, tính tạm tính
                    $prov = $this->calculateProvisionalSalary($emp, $m, $nam);
                    // Chỉ đưa vào danh sách nếu có đi làm hoặc có dữ liệu thực tế
                    // Để tránh hiển thị quá nhiều tháng trống trong năm
                    if ($prov->tong_ngay_cong > 0 || $prov->so_ngay_nghi_khong_phep > 0 || $prov->so_ngay_nghi_phep > 0) {
                        $monthlyData->push($prov);
                        
                        $tongThucNhan += $prov->luong_thuc_nhan;
                        $tongNgayCong += $prov->tong_ngay_cong;
                        $tongGioTangCa += $prov->tong_gio_tang_ca;
                        $tongPhat += $prov->phat;
                    }
                }
            }

            $emp->monthly_data = $monthlyData;
            $emp->summary = (object)[
                'tong_thuc_nhan' => $tongThucNhan,
                'tong_ngay_cong' => $tongNgayCong,
                'tong_gio_tang_ca' => $tongGioTangCa,
                'tong_phat' => $tongPhat,
                'so_thang_da_chot' => $soThangDaChot,
                'tong_thang' => $monthlyData->count()
            ];

            return $emp;
        });

        // Thống kê nhanh toàn cục cho khoảng thời gian đã chọn
        $queryThongKe = BangLuong::whereIn('thang', $months)->where('nam', $nam);
        if ($user->rap_chieu_phim_id !== null) {
            $queryThongKe->whereHas('nguoiDung', function($q) use ($user) {
                $q->where('rap_chieu_phim_id', $user->rap_chieu_phim_id);
            });
        }
        $queryDaThanhToan = clone $queryThongKe;

        $thongKe = [
            'tong_chi_tra' => $queryThongKe->sum('luong_thuc_nhan'),
            'da_thanh_toan' => $queryDaThanhToan->where('trang_thai', 'da_thanh_toan')->sum('luong_thuc_nhan'),
            // Số nhân viên được tính là số bản ghi chia cho số tháng
            'so_nhan_vien_da_chot' => $queryThongKe->distinct('nguoi_dung_id')->count(),
        ];

        return view('admin.bang-luongs.index', compact('employeesPaginator', 'nhanViens', 'loaiLoc', 'thang', 'quy', 'nam', 'thongKe'));
    }

    public function showCalculateForm(Request $request)
    {
        $user = auth()->user();
        
        $nvQuery = NguoiDung::where('vai_tro', 'nhan_vien');
        if ($user->rap_chieu_phim_id !== null) {
            $nvQuery->where('rap_chieu_phim_id', $user->rap_chieu_phim_id);
        }
        $nhanViens = $nvQuery->get();

        $thang = $request->input('thang', date('m'));
        $nam = $request->input('nam', date('Y'));
        
        $nhanVienSelectedId = $request->input('nhan_vien_id');
        $dataCalculated = null;
        $nhanVienSelected = null;

        if ($nhanVienSelectedId) {
            $nhanVienSelected = NguoiDung::find($nhanVienSelectedId);
            
            if ($user->rap_chieu_phim_id !== null && $nhanVienSelected->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
                abort(403);
            }

            $prov = $this->calculateProvisionalSalary($nhanVienSelected, $thang, $nam);
            
            $dataCalculated = [
                'tong_ngay_cong' => $prov->tong_ngay_cong,
                'tong_gio_lam' => $prov->tong_gio_lam,
                'tong_gio_tang_ca' => $prov->tong_gio_tang_ca,
                'so_lan_di_muon' => $prov->so_lan_di_muon,
                'so_lan_ve_som' => $prov->so_lan_ve_som,
                'so_ngay_nghi_phep' => $prov->so_ngay_nghi_phep,
                'so_ngay_nghi_khong_phep' => $prov->so_ngay_nghi_khong_phep,
                'luong_co_ban' => $prov->luong_co_ban,
                'luong_thoi_gian' => $prov->luong_thoi_gian,
                'luong_tang_ca' => $prov->luong_tang_ca,
                'phat_tu_dong' => $prov->phat,
                'luong_thuc_nhan_tam_tinh' => $prov->luong_thuc_nhan,
            ];
        }

        return view('admin.bang-luongs.calculate', compact(
            'nhanViens',
            'thang',
            'nam',
            'nhanVienSelectedId',
            'nhanVienSelected',
            'dataCalculated'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nguoi_dung_id' => 'required|exists:nguoi_dungs,id',
            'thang' => 'required|integer|min:1|max:12',
            'nam' => 'required|integer|min:2020|max:2050',
            'tong_ngay_cong' => 'required|numeric|min:0',
            'tong_gio_lam' => 'required|numeric|min:0',
            'tong_gio_tang_ca' => 'required|numeric|min:0',
            'so_lan_di_muon' => 'required|integer|min:0',
            'so_lan_ve_som' => 'required|integer|min:0',
            'so_ngay_nghi_phep' => 'required|integer|min:0',
            'so_ngay_nghi_khong_phep' => 'required|integer|min:0',
            'luong_co_ban' => 'required|numeric|min:0',
            'phu_cap' => 'nullable|numeric|min:0',
            'thuong' => 'nullable|numeric|min:0',
            'phat' => 'nullable|numeric|min:0',
            'luong_thuc_nhan' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $employee = NguoiDung::findOrFail($request->nguoi_dung_id);

        // Bảo mật chi nhánh
        if ($user->rap_chieu_phim_id !== null && $employee->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403);
        }

        // Chốt hoặc Cập nhật bảng lương
        BangLuong::updateOrCreate(
            [
                'nguoi_dung_id' => $request->nguoi_dung_id,
                'thang' => $request->thang,
                'nam' => $request->nam,
            ],
            [
                'tong_ngay_cong' => $request->tong_ngay_cong,
                'tong_gio_lam' => $request->tong_gio_lam,
                'tong_gio_tang_ca' => $request->tong_gio_tang_ca,
                'so_lan_di_muon' => $request->so_lan_di_muon,
                'so_lan_ve_som' => $request->so_lan_ve_som,
                'so_ngay_nghi_phep' => $request->so_ngay_nghi_phep,
                'so_ngay_nghi_khong_phep' => $request->so_ngay_nghi_khong_phep,
                'luong_co_ban' => $request->luong_co_ban,
                'phu_cap' => $request->phu_cap ?? 0,
                'thuong' => $request->thuong ?? 0,
                'phat' => $request->phat ?? 0,
                'luong_thuc_nhan' => $request->luong_thuc_nhan,
                'trang_thai' => 'chua_thanh_toan',
            ]
        );

        return redirect()->route('admin.bang-luongs.index')
            ->with('success', "Đã chốt bảng lương tháng {$request->thang}/{$request->nam} của nhân viên {$employee->ho_ten} thành công!");
    }

    public function togglePaymentStatus(BangLuong $bangLuong)
    {
        $user = auth()->user();

        // Bảo mật chi nhánh
        if ($user->rap_chieu_phim_id !== null && $bangLuong->nguoiDung->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403);
        }

        $newStatus = $bangLuong->trang_thai === 'da_thanh_toan' ? 'chua_thanh_toan' : 'da_thanh_toan';
        $bangLuong->update(['trang_thai' => $newStatus]);

        return back()->with('success', 'Cập nhật trạng thái thanh toán lương thành công!');
    }

    public function destroy(BangLuong $bangLuong)
    {
        $user = auth()->user();

        // Bảo mật chi nhánh
        if ($user->rap_chieu_phim_id !== null && $bangLuong->nguoiDung->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403);
        }

        $bangLuong->delete();

        return redirect()->route('admin.bang-luongs.index')->with('success', 'Xóa bảng lương thành công!');
    }
}
