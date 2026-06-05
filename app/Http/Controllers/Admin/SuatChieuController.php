<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuatChieuController extends Controller
{
    private const THOI_GIAN_DON_PHONG = 15;

    public function index()
    {
        $suatChieus = Showtime::with(['movie', 'cinema'])
            ->orderByDesc('show_date')
            ->orderByDesc('show_time')
            ->paginate(15);

        return view('admin.suat-chieu.index', compact('suatChieus'));
    }

    public function create()
    {
        $phims = Movie::where('status', '!=', 'stopped')->orderBy('title')->get();
        $raps = Cinema::where('status', 'active')->orderBy('name')->get();
        $phongChieuMacDinh = ['Phong 1', 'Phong 2', 'Phong 3', 'Phong VIP'];

        return view('admin.suat-chieu.create', compact('phims', 'raps', 'phongChieuMacDinh'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'movie_id' => ['required', 'exists:movies,id'],
            'cinema_id' => ['required', 'exists:cinemas,id'],
            'room_name' => ['required', 'string', 'max:100'],
            'show_date' => ['required', 'date', 'after_or_equal:today'],
            'show_time' => ['required', 'date_format:H:i'],
            'price' => ['required', 'integer', 'min:1000', 'max:500000'],
        ], [
            'show_date.after_or_equal' => 'Ngay chieu khong duoc nam trong qua khu.',
            'show_time.date_format' => 'Gio chieu khong dung dinh dang.',
        ]);

        $phim = Movie::findOrFail($data['movie_id']);
        $rap = Cinema::findOrFail($data['cinema_id']);

        if ($phim->status === 'stopped') {
            throw ValidationException::withMessages([
                'movie_id' => 'Phim da ngung chieu nen khong the tao suat moi.',
            ]);
        }

        if ($rap->status !== 'active') {
            throw ValidationException::withMessages([
                'cinema_id' => 'Rap nay dang tam dung hoat dong.',
            ]);
        }

        $batDau = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['show_date'] . ' ' . $data['show_time'],
            'Asia/Ho_Chi_Minh'
        );

        if ($batDau->lte(Carbon::now('Asia/Ho_Chi_Minh'))) {
            throw ValidationException::withMessages([
                'show_time' => 'Suat chieu phai lon hon thoi diem hien tai.',
            ]);
        }

        if ($phim->release_date && $batDau->toDateString() < $phim->release_date->toDateString()) {
            throw ValidationException::withMessages([
                'show_date' => 'Khong the tao suat chieu truoc ngay khoi chieu cua phim.',
            ]);
        }

        $thoiLuongPhim = max((int) $phim->duration, 1);
        $ketThucCoDonPhong = $batDau->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);
        $suatTrung = $this->timSuatChieuBiTrung(
            (int) $data['cinema_id'],
            $data['room_name'],
            $data['show_date'],
            $batDau,
            $ketThucCoDonPhong
        );

        if ($suatTrung) {
            throw ValidationException::withMessages([
                'show_time' => 'Phong nay da co suat "' . ($suatTrung->movie?->title ?? 'Phim da xoa') . '" luc '
                    . Carbon::parse($suatTrung->show_time)->format('H:i')
                    . '. Can cach nhau toi thieu ' . self::THOI_GIAN_DON_PHONG . ' phut de don phong.',
            ]);
        }

        $suatChieu = Showtime::create($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_showtime',
            'module' => 'showtimes',
            'description' => 'Tao suat chieu ' . $phim->title . ' tai ' . $rap->name,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'properties' => ['showtime_id' => $suatChieu->id],
        ]);

        return redirect()
            ->route('admin.suat-chieu.index')
            ->with('success', 'Da tao suat chieu moi.');
    }

    private function timSuatChieuBiTrung(
        int $rapId,
        string $phongChieu,
        string $ngayChieu,
        Carbon $batDauMoi,
        Carbon $ketThucMoi
    ): ?Showtime {
        return Showtime::with('movie')
            ->where('cinema_id', $rapId)
            ->where('room_name', $phongChieu)
            ->whereDate('show_date', $ngayChieu)
            ->get()
            ->first(function (Showtime $suatChieu) use ($batDauMoi, $ketThucMoi) {
                $batDauCu = Carbon::parse($suatChieu->show_date . ' ' . $suatChieu->show_time, 'Asia/Ho_Chi_Minh');
                $thoiLuongCu = max((int) $suatChieu->movie?->duration, 1);
                $ketThucCu = $batDauCu->copy()->addMinutes($thoiLuongCu + self::THOI_GIAN_DON_PHONG);

                return $batDauMoi->lt($ketThucCu) && $ketThucMoi->gt($batDauCu);
            });
    }
}
