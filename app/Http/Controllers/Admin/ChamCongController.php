<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChamCong;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChamCongController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ChamCong::with('nguoiDung');

        // Phân quyền theo chi nhánh
        if ($user->rap_chieu_phim_id !== null) {
            $query->whereHas('nguoiDung', function ($q) use ($user) {
                $q->where('rap_chieu_phim_id', $user->rap_chieu_phim_id)
                  ->where('vai_tro', 'nhan_vien');
            });
        } else {
            $query->whereHas('nguoiDung', function ($q) {
                $q->where('vai_tro', 'nhan_vien');
            });
        }

        // Tìm kiếm và lọc
        if ($request->filled('keyword')) {
            $query->whereHas('nguoiDung', function ($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('ngay')) {
            $query->whereDate('ngay', $request->ngay);
        }

        if ($request->filled('thang')) {
            $parts = explode('-', $request->thang);
            if (count($parts) === 2) {
                $query->whereYear('ngay', $parts[0])
                      ->whereMonth('ngay', $parts[1]);
            }
        }

        $chamCongs = $query->orderBy('ngay', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.cham-congs.index', compact('chamCongs'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Lấy danh sách nhân viên thuộc chi nhánh quản lý
        $query = NguoiDung::where('vai_tro', 'nhan_vien');
        if ($user->rap_chieu_phim_id !== null) {
            $query->where('rap_chieu_phim_id', $user->rap_chieu_phim_id);
        }
        $nhanViens = $query->get();

        return view('admin.cham-congs.create', compact('nhanViens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nguoi_dung_id' => 'required|exists:nguoi_dungs,id',
            'ngay' => 'required|date',
            'loai_cham_cong' => 'required|in:di_lam,nghi_phep,nghi_khong_phep',
            'gio_vao' => 'nullable|required_if:loai_cham_cong,di_lam',
            'gio_ra' => 'nullable|required_if:loai_cham_cong,di_lam|after:gio_vao',
            'so_gio_tang_ca' => 'nullable|numeric|min:0|max:16',
            'ghi_chu' => 'nullable|string|max:255',
        ]);

        $nguoiDungId = $request->nguoi_dung_id;
        $ngay = $request->ngay;

        // Kiểm tra trùng lặp chấm công trong cùng ngày
        $existing = ChamCong::where('nguoi_dung_id', $nguoiDungId)
            ->where('ngay', $ngay)
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'Nhân viên này đã được chấm công trong ngày ' . Carbon::parse($ngay)->format('d/m/Y') . ' rồi!');
        }

        $data = [
            'nguoi_dung_id' => $nguoiDungId,
            'ngay' => $ngay,
            'ghi_chu' => $request->ghi_chu,
        ];

        if ($request->loai_cham_cong === 'di_lam') {
            $gioVao = $request->gio_vao;
            $gioRa = $request->gio_ra;
            
            $data['gio_vao'] = $gioVao;
            $data['gio_ra'] = $gioRa;

            // Tính toán đi muộn / về sớm
            // Quy chuẩn: Vào ca lúc 08:00, ra ca lúc 17:00
            $gioVaoChuan = Carbon::createFromFormat('H:i', '08:00');
            $gioRaChuan = Carbon::createFromFormat('H:i', '17:00');
            
            $timeVao = Carbon::createFromFormat('H:i', $gioVao);
            $timeRa = Carbon::createFromFormat('H:i', $gioRa);

            $data['di_muon'] = $timeVao->gt($gioVaoChuan->addMinutes(5)); // Đi muộn nếu sau 08:05
            $data['ve_som'] = $timeRa->lt($gioRaChuan);

            // Tính giờ làm thực tế (trừ 1 tiếng nghỉ trưa từ 12:00 - 13:00)
            $phutLam = $timeRa->diffInMinutes($timeVao);
            
            // Nếu giờ làm bao phủ qua thời gian nghỉ trưa, trừ 60 phút
            $lunchStart = Carbon::createFromFormat('H:i', '12:00');
            $lunchEnd = Carbon::createFromFormat('H:i', '13:00');
            if ($timeVao->lt($lunchStart) && $timeRa->gt($lunchEnd)) {
                $phutLam -= 60;
            }

            $soGioLam = round($phutLam / 60, 2);
            $data['so_gio_lam'] = max(0, min(8, $soGioLam)); // Giờ làm chuẩn tối đa 8 tiếng

            // Tính giờ tăng ca tự động hoặc lấy từ input
            if ($request->filled('so_gio_tang_ca')) {
                $data['so_gio_tang_ca'] = $request->so_gio_tang_ca;
            } else {
                // Tự động tính tăng ca nếu làm sau 17:00
                if ($timeRa->gt($gioRaChuan)) {
                    $phutTangCa = $timeRa->diffInMinutes($gioRaChuan);
                    $data['so_gio_tang_ca'] = round($phutTangCa / 60, 2);
                } else {
                    $data['so_gio_tang_ca'] = 0.00;
                }
            }

            $data['nghi_phep'] = false;
            $data['nghi_khong_phep'] = false;
        } elseif ($request->loai_cham_cong === 'nghi_phep') {
            $data['gio_vao'] = null;
            $data['gio_ra'] = null;
            $data['so_gio_lam'] = 0.00;
            $data['so_gio_tang_ca'] = 0.00;
            $data['di_muon'] = false;
            $data['ve_som'] = false;
            $data['nghi_phep'] = true;
            $data['nghi_khong_phep'] = false;
        } else { // nghi_khong_phep
            $data['gio_vao'] = null;
            $data['gio_ra'] = null;
            $data['so_gio_lam'] = 0.00;
            $data['so_gio_tang_ca'] = 0.00;
            $data['di_muon'] = false;
            $data['ve_som'] = false;
            $data['nghi_phep'] = false;
            $data['nghi_khong_phep'] = true;
        }

        ChamCong::create($data);

        return redirect()->route('admin.cham-congs.index')->with('success', 'Chấm công thành công!');
    }

    public function edit(ChamCong $chamCong)
    {
        $user = auth()->user();

        // Kiểm tra phân quyền chi nhánh
        if ($user->rap_chieu_phim_id !== null && $chamCong->nguoiDung->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa chấm công của nhân viên chi nhánh khác.');
        }

        return view('admin.cham-congs.edit', compact('chamCong'));
    }

    public function update(Request $request, ChamCong $chamCong)
    {
        $user = auth()->user();

        // Kiểm tra phân quyền chi nhánh
        if ($user->rap_chieu_phim_id !== null && $chamCong->nguoiDung->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403);
        }

        $request->validate([
            'loai_cham_cong' => 'required|in:di_lam,nghi_phep,nghi_khong_phep',
            'gio_vao' => 'nullable|required_if:loai_cham_cong,di_lam',
            'gio_ra' => 'nullable|required_if:loai_cham_cong,di_lam|after:gio_vao',
            'so_gio_tang_ca' => 'nullable|numeric|min:0|max:16',
            'ghi_chu' => 'nullable|string|max:255',
        ]);

        $data = [
            'ghi_chu' => $request->ghi_chu,
        ];

        if ($request->loai_cham_cong === 'di_lam') {
            $gioVao = $request->gio_vao;
            $gioRa = $request->gio_ra;
            
            $data['gio_vao'] = $gioVao;
            $data['gio_ra'] = $gioRa;

            $gioVaoChuan = Carbon::createFromFormat('H:i', '08:00');
            $gioRaChuan = Carbon::createFromFormat('H:i', '17:00');
            
            $timeVao = Carbon::createFromFormat('H:i', $gioVao);
            $timeRa = Carbon::createFromFormat('H:i', $gioRa);

            $data['di_muon'] = $timeVao->gt($gioVaoChuan->addMinutes(5));
            $data['ve_som'] = $timeRa->lt($gioRaChuan);

            $phutLam = $timeRa->diffInMinutes($timeVao);
            
            $lunchStart = Carbon::createFromFormat('H:i', '12:00');
            $lunchEnd = Carbon::createFromFormat('H:i', '13:00');
            if ($timeVao->lt($lunchStart) && $timeRa->gt($lunchEnd)) {
                $phutLam -= 60;
            }

            $soGioLam = round($phutLam / 60, 2);
            $data['so_gio_lam'] = max(0, min(8, $soGioLam));

            if ($request->filled('so_gio_tang_ca')) {
                $data['so_gio_tang_ca'] = $request->so_gio_tang_ca;
            } else {
                if ($timeRa->gt($gioRaChuan)) {
                    $phutTangCa = $timeRa->diffInMinutes($gioRaChuan);
                    $data['so_gio_tang_ca'] = round($phutTangCa / 60, 2);
                } else {
                    $data['so_gio_tang_ca'] = 0.00;
                }
            }

            $data['nghi_phep'] = false;
            $data['nghi_khong_phep'] = false;
        } elseif ($request->loai_cham_cong === 'nghi_phep') {
            $data['gio_vao'] = null;
            $data['gio_ra'] = null;
            $data['so_gio_lam'] = 0.00;
            $data['so_gio_tang_ca'] = 0.00;
            $data['di_muon'] = false;
            $data['ve_som'] = false;
            $data['nghi_phep'] = true;
            $data['nghi_khong_phep'] = false;
        } else {
            $data['gio_vao'] = null;
            $data['gio_ra'] = null;
            $data['so_gio_lam'] = 0.00;
            $data['so_gio_tang_ca'] = 0.00;
            $data['di_muon'] = false;
            $data['ve_som'] = false;
            $data['nghi_phep'] = false;
            $data['nghi_khong_phep'] = true;
        }

        $chamCong->update($data);

        return redirect()->route('admin.cham-congs.index')->with('success', 'Cập nhật chấm công thành công!');
    }

    public function destroy(ChamCong $chamCong)
    {
        $user = auth()->user();

        // Kiểm tra phân quyền chi nhánh
        if ($user->rap_chieu_phim_id !== null && $chamCong->nguoiDung->rap_chieu_phim_id !== $user->rap_chieu_phim_id) {
            abort(403);
        }

        $chamCong->delete();

        return redirect()->route('admin.cham-congs.index')->with('success', 'Xóa chấm công thành công!');
    }
}
