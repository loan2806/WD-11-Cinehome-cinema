<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Phims;
use App\Models\Showtime;
use Illuminate\Http\Request;

class DatVeController extends Controller
{
    public function chonRap()
    {
        // Lấy danh sách tất cả các rạp
        $danhSachRap = Cinema::all();
        
        // Nếu đề bài yêu cầu "làm theo 1 rạp" có thể hiểu là rạp mặc định
        // Nhưng ở đây vẫn hiển thị danh sách để người dùng chọn
        return view('dat_ve.chon_rap', compact('danhSachRap'));
    }

    public function chonPhim($rap_id)
    {
        $rap = Cinema::findOrFail($rap_id);
        
        // Lấy danh sách suất chiếu của rạp này, từ hôm nay trở đi
        $danhSachSuatChieu = Showtime::with('movie')
            ->where('cinema_id', $rap_id)
            ->where('show_date', '>=', date('Y-m-d'))
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get();
            
        // Gom nhóm theo ID phim
        $suatChieuTheoPhim = $danhSachSuatChieu->groupBy('movie_id');

        return view('dat_ve.chon_phim', compact('rap', 'suatChieuTheoPhim'));
    }

    public function chonGhe($suat_chieu_id)
    {
        $suatChieu = Showtime::with(['movie', 'cinema'])->findOrFail($suat_chieu_id);
        
        // Danh sách ghế đã được mua (giả lập mảng rỗng ban đầu, thực tế sẽ lấy từ DB vé)
        $gheDaDat = []; 
        
        // Cấu hình sơ đồ ghế
        $hangGhe = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $soCot = 10;

        return view('dat_ve.chon_ghe', compact('suatChieu', 'gheDaDat', 'hangGhe', 'soCot'));
    }
}
