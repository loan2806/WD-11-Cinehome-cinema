<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhongChieuRequest;
use App\Http\Requests\Admin\UpdatePhongChieuRequest;
use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LichBaoTriGheNgoi;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;
use App\Services\AdminNotificationService;
use App\Services\SeatGeneratorService;
use App\Services\SeatMaintenanceService;

use App\Traits\Loggable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PhongChieuController extends Controller
{
    use Loggable;
    protected SeatGeneratorService $seatGenerator;

    public function __construct(SeatGeneratorService $seatGenerator)
    {
        $this->seatGenerator = $seatGenerator;
    }

    public function index(Request $request): View
    {
        $query = PhongChieu::with(['rapChieuPhim', 'hangGhes', 'gheNgois']);

        if ($request->has('rap_chieu_phim_id') && $request->rap_chieu_phim_id) {
            $query->where('rap_chieu_phim_id', $request->rap_chieu_phim_id);
        }

        if ($request->has('trang_thai') && $request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $phongChieus = $query->withTrashed()
            ->orderBy('rap_chieu_phim_id')
            ->orderBy('ten_phong')
            ->paginate(15)
            ->withQueryString();

        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        return view('admin.phong-chieus.index', compact('phongChieus', 'rapChieuPhims'));
    }

    public function create(): View
    {
        $rapChieuPhims = RapChieuPhim::where('trang_thai', 'hoat_dong')
            ->orderBy('ten_rap')
            ->get();

        return view('admin.phong-chieus.create', compact('rapChieuPhims'));
    }

    public function store(StorePhongChieuRequest $request)
    {
        $data = $request->validated();
        
        PhongChieu::create($data);

        $this->ghiNhatKy($request, 'Thêm phòng chiếu', 'Quản lý phòng & ghế', "Thêm phòng: {$data['ten_phong']}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PhongChieu $phongChieu): View
{
    // 1. Tự động quét và cập nhật trạng thái lịch bảo trì
    $this->processMaintenanceSchedules($phongChieu->id);

    $phongChieu->load([
        'rapChieuPhim',
        'hangGhes.gheNgois.loaiGhe',
        'suatChieus.phim',
    ]);

    $seatMap = $this->seatGenerator->getSeatMap($phongChieu);
    $soHang = $phongChieu->hangGhes->count();
    $soCot = $phongChieu->gheNgois->count() > 0
        ? $phongChieu->gheNgois->max('cot')
        : 0;

    // 2. Lấy danh sách lịch bảo trì
    $lichBaoTris = LichBaoTriGheNgoi::where('phong_chieu_id', $phongChieu->id)
        ->with(['gheNgoi', 'nguoiDung'])
        ->where('trang_thai', 'cho_thuc_hien')
        ->where('thoi_gian_bat_dau', '>', now())
        ->orderBy('thoi_gian_bat_dau')
        ->get();

    // 🌟 3. THÊM DÒNG NÀY: Lấy danh sách Loại Ghế từ DB
    $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

    return view('admin.phong-chieus.show', compact(
        'phongChieu',
        'seatMap',
        'soHang',
        'soCot',
        'lichBaoTris',
        'loaiGhes' // 🌟 BỔ SUNG 'loaiGhes' VÀO ĐÂY
    ));
}

    public function edit(PhongChieu $phongChieu): View
    {
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        return view('admin.phong-chieus.edit', compact('phongChieu', 'rapChieuPhims'));
    }

    public function update(UpdatePhongChieuRequest $request, PhongChieu $phongChieu)
    {
        $data = $request->validated();

        if (isset($data['trang_thai']) && $data['trang_thai'] === 'bao_tri' && $phongChieu->trang_thai !== 'bao_tri') {
            
            $suatChieuSaps = SuatChieu::where('phong_chieu_id', $phongChieu->id)
                ->where(function ($q) {
                    $q->where('thoi_gian_chieu', '>=', now())
                      ->orWhereRaw("DATE_ADD(thoi_gian_chieu, INTERVAL COALESCE(thoi_luong, 120) MINUTE) >= ?", [now()->format('Y-m-d H:i:s')]);
                })
                ->with('phim')
                ->get();

            if ($suatChieuSaps->isNotEmpty()) {
                $count = $suatChieuSaps->count();
                $tenPhims = $suatChieuSaps->pluck('phim.ten_phim')->filter()->unique()->take(3)->join(', ');
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Không thể chuyển phòng sang BẢO TRÌ vì đang có {$count} suất chiếu chưa hoàn tất (Phim: {$tenPhims}). Vui lòng hủy hoặc dời suất chiếu trước.");
            }

            $tenRap = optional($phongChieu->rapChieuPhim)->ten_rap;
            $hasTickets = VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
                ->where('ten_phong', $phongChieu->ten_phong)
                ->when($tenRap, fn($q) => $q->where('ten_rap', $tenRap))
                ->where('thoi_gian_het_han', '>', now())
                ->exists();

            if ($hasTickets) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Không thể chuyển phòng sang BẢO TRÌ vì vẫn còn vé xem phim đã đặt chưa hết hạn trong phòng này.");
            }
        }
        
        $phongChieu->update($data);

        $this->ghiNhatKy($request, 'Cập nhật phòng chiếu', 'Quản lý phòng & ghế', "Cập nhật phòng: {$phongChieu->ten_phong}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được cập nhật thành công.');
    }

    public function destroy(Request $request, PhongChieu $phongChieu)
    {
        if ($phongChieu->suatChieus()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa phòng chiếu vì đang có suất chiếu.');
        }

        $tenPhong = $phongChieu->ten_phong;
        $phongChieu->delete();

        AdminNotificationService::push(
            '🗑️ Xóa phòng chiếu',
            "Đã xóa phòng {$tenPhong}",
            'Warning'
        );

        $this->ghiNhatKy($request, 'Xóa phòng chiếu', 'Quản lý phòng & ghế', "Xóa phòng: {$tenPhong}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa.');
    }

    public function forceDestroy(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->forceDelete();

        $this->ghiNhatKy($request, 'Xóa vĩnh viễn phòng chiếu', 'Quản lý phòng & ghế', "Xóa vĩnh viễn phòng #{$id}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa vĩnh viễn.');
    }

    public function restore(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->restore();

        $this->ghiNhatKy($request, 'Khôi phục phòng chiếu', 'Quản lý phòng & ghế', "Khôi phục phòng #{$id}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được khôi phục.');
    }

    public function generateSeats(Request $request, PhongChieu $phongChieu)
    {
        if ($request->has('reset_all') && $request->reset_all == '1') {
            try {
                $phongChieu->gheNgois()->delete();
                $phongChieu->hangGhes()->delete();

                return redirect()
                    ->route('admin.phong-chieus.show', $phongChieu)
                    ->with('success', 'Đã xóa toàn bộ ghế trong phòng!');
            } catch (\Exception $e) {
                return redirect()
                    ->route('admin.phong-chieus.show', $phongChieu)
                    ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        $loaiGheCoupleId = $request->loai_ghe_couple_id;
        if (!$loaiGheCoupleId) {
            $coupleSeat = LoaiGhe::where('la_couple', true)->first();
            $loaiGheCoupleId = $coupleSeat?->id;
        }

        $request->validate([
            'so_hang' => 'required|integer|min:1|max:20',
            'so_cot' => 'required|integer|min:1|max:20',
            'loai_ghe_thuong_id' => 'required|exists:loai_ghes,id',
            'loai_ghe_vip_id' => 'nullable|exists:loai_ghes,id',
            'loai_ghe_couple_id' => 'nullable|exists:loai_ghes,id',
        ]);

        $currentSeats = $phongChieu->gheNgois;
        $currentRows = $currentSeats->pluck('hangGhe.ten_hang')->unique()->count();
        $currentCols = $currentSeats->max('vi_tri_cot') ?? 0;
        $totalCapacity = $currentRows * $currentCols;

        if ($currentSeats->count() >= $totalCapacity && $totalCapacity > 0) {
            return redirect()
                ->route('admin.phong-chieus.show', $phongChieu)
                ->with('error', 'Phòng đã đầy ghế! Vui lòng reset phòng trước khi tạo ghế mới.');
        }

        try {
            $ketQua = $this->seatGenerator->generateSeats(
                $phongChieu,
                (int) $request->so_hang,
                (int) $request->so_cot,
                (int) $request->loai_ghe_thuong_id,
                $request->loai_ghe_vip_id,
                $loaiGheCoupleId,
                true
            );

            return redirect()
                ->route('admin.phong-chieus.show', $phongChieu)
                ->with('success', "Đã tạo {$ketQua['tong_so_ghe']} ghế thành công!");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.phong-chieus.show', $phongChieu)
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function toggleSeatMaintenance(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'ghe_id' => 'nullable|exists:ghe_ngois,id',
            'ghe_ids' => 'nullable|array',
            'ghe_ids.*' => 'exists:ghe_ngois,id',
        ]);

        $gheIds = $request->ghe_ids ?? [$request->ghe_id];
        if (empty($gheIds)) {
            return response()->json(['success' => false, 'message' => 'Thiếu ID ghế.'], 422);
        }

        $ghe = $phongChieu->gheNgois()->whereIn('id', $gheIds)->first();
        if (!$ghe) {
            return response()->json(['success' => false, 'message' => 'Ghế không tồn tại.'], 404);
        }

        $service = app(SeatMaintenanceService::class);
        $isMaintenance = $ghe->trang_thai !== 'bao_tri';
        $removedScheduleIds = [];

        if ($isMaintenance) {
            $allConflictList = [];
            $gheIdsToMaintain = [];

            foreach ($gheIds as $gheId) {
                $seat = $phongChieu->gheNgois()->findOrFail($gheId);
                if ($seat->trang_thai !== 'hoat_dong' && $seat->trang_thai !== 'sap_bao_tri') continue;
                
                $result = $service->canMaintainNow($seat);
                if (!$result['can']) {
                    $allConflictList = array_merge($allConflictList, $result['conflicts']);
                } else {
                    $gheIdsToMaintain[] = $seat;
                }
            }

            if (!empty($allConflictList)) {
                $uniqueConflicts = collect($allConflictList)->unique('ve_id');
                return response()->json([
                    'success' => false,
                    'message' => 'Ghế đã chọn có vé ở suất chiếu tương lai. Không thể bảo trì ngay. Hãy xử lý ' . $uniqueConflicts->count() . ' vé liên quan trước.',
                    'conflicts_count' => $uniqueConflicts->count(),
                    'conflicts' => $uniqueConflicts->take(5)->values()->all(),
                ], 422);
            }

            foreach ($gheIdsToMaintain as $seat) {
                $service->maintainNow($seat, auth()->id());
            }
        } else {
            foreach ($gheIds as $gheId) {
                $seat = $phongChieu->gheNgois()->findOrFail($gheId);
                if ($seat->trang_thai !== 'bao_tri') continue;

                $pending = LichBaoTriGheNgoi::where('ghe_ngoi_id', $seat->id)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->latest()
                    ->first();
                if ($pending) {
                    $removedScheduleIds[] = $pending->id;
                    try {
                        $service->completeMaintenance($pending, auth()->id());
                    } catch (\Exception $e) {
                        \Log::error('Lỗi completeMaintenance trong toggleSeatMaintenance: ' . $e->getMessage());
                        $service->activateSeat($seat, auth()->id());
                    }
                } else {
                    $seat->update(['trang_thai' => 'hoat_dong']);
                }
            }
        }

        $updatedSeats = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'loai_ghe' => $g->loaiGhe->ten_loai ?? 'Thường',
                'loai_ghe_id' => $g->loai_ghe_id,
                'mau_sac' => $g->loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $g->loaiGhe->phu_thu ?? 0,
                'trang_thai' => $g->trang_thai,
            ])->toArray();

        $count = $isMaintenance
            ? count($gheIdsToMaintain ?? $gheIds)
            : count($gheIds);

        return response()->json([
            'success' => true,
            'message' => $isMaintenance
                ? "Đã chuyển {$count} ghế sang bảo trì."
                : "Đã kích hoạt lại {$count} ghế.",
            'is_maintenance' => $isMaintenance,
            'trang_thai' => $isMaintenance ? 'bao_tri' : 'hoat_dong',
            'updated_seats' => $updatedSeats,
            'removed_schedule_ids' => $removedScheduleIds,
            'expired_schedule_ids' => $removedScheduleIds,
        ]);
    }

    /**
     * LÊN LỊCH BẢO TRÌ CÓ THỜI HẠN CHO GHẾ
     */
    public function scheduleSeatMaintenance(Request $request, PhongChieu $phongChieu)
    {
        $validated = $request->validate([
            'ghe_ids' => 'required|array|min:1',
            'ghe_ids.*' => 'exists:ghe_ngois,id',
            'thoi_gian_bat_dau' => 'required|date',
            'thoi_gian_ket_thuc' => 'nullable|date|after:thoi_gian_bat_dau',
            'ly_do' => 'required|string|max:500',
        ], [
            'thoi_gian_bat_dau.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'thoi_gian_ket_thuc.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'ly_do.required' => 'Vui lòng nhập lý do bảo trì.',
        ]);

        $gheIds = $validated['ghe_ids'];
        $thoiGianBatDau = Carbon::parse($validated['thoi_gian_bat_dau']);
        $thoiGianKetThuc = !empty($validated['thoi_gian_ket_thuc']) ? Carbon::parse($validated['thoi_gian_ket_thuc']) : null;

        if ($thoiGianBatDau->isPast()) {
            $thoiGianBatDau = now();
        }

        $createdSchedules = [];
        $updatedSeats = [];
        $errors = [];

        $gheList = GheNgoi::whereIn('id', $gheIds)
            ->where('phong_chieu_id', $phongChieu->id)
            ->get()
            ->keyBy('id');

        foreach ($gheIds as $gheId) {
            $seat = $gheList->get($gheId);
            if (!$seat) continue;

            // 1. Kiểm tra vé đã bán thuộc khoảng thời gian bảo trì
            $conflicted = $this->hasConflictInPeriod($seat, $thoiGianBatDau, $thoiGianKetThuc);
            if ($conflicted) {
                $errors[] = "Ghế {$seat->ma_ghe} đã có khách đặt vé trong khoảng thời gian này.";
                continue;
            }

            if ($seat->trang_thai === 'bao_tri') {
                continue;
            }

            // 2. Kiểm tra lịch bảo trì trùng lặp
            $lichTrungLap = LichBaoTriGheNgoi::where('ghe_ngoi_id', $seat->id)
                ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                ->where(function ($q) use ($thoiGianBatDau, $thoiGianKetThuc) {
                    if ($thoiGianKetThuc) {
                        $q->whereBetween('thoi_gian_bat_dau', [$thoiGianBatDau, $thoiGianKetThuc])
                           ->orWhereBetween('thoi_gian_ket_thuc', [$thoiGianBatDau, $thoiGianKetThuc])
                           ->orWhere(function ($q3) use ($thoiGianBatDau, $thoiGianKetThuc) {
                               $q3->where('thoi_gian_bat_dau', '<=', $thoiGianBatDau)
                                  ->where('thoi_gian_ket_thuc', '>=', $thoiGianKetThuc);
                           });
                    } else {
                        $q->where('thoi_gian_bat_dau', '>=', $thoiGianBatDau);
                    }
                })
                ->exists();

            if ($lichTrungLap) {
                $errors[] = "Ghế {$seat->ma_ghe} đã có lịch bảo trì trùng lặp trong thời gian này.";
                continue;
            }

            // 3. Kiểm tra suất chiếu trùng khoảng thời gian bảo trì
            $suatChieuQuery = SuatChieu::where('phong_chieu_id', $phongChieu->id);
            if ($thoiGianKetThuc) {
                $suatChieuQuery->where('thoi_gian_chieu', '<', $thoiGianKetThuc)
                    ->whereRaw("DATE_ADD(thoi_gian_chieu, INTERVAL COALESCE(thoi_luong, 120) MINUTE) > '" . $thoiGianBatDau->format('Y-m-d H:i:s') . "'");
            } else {
                $suatChieuQuery->whereRaw("DATE_ADD(thoi_gian_chieu, INTERVAL COALESCE(thoi_luong, 120) MINUTE) > '" . $thoiGianBatDau->format('Y-m-d H:i:s') . "'");
            }

            if ($suatChieuQuery->exists()) {
                $errors[] = "Ghế {$seat->ma_ghe}: Có suất chiếu trùng với khoảng thời gian bảo trì đã chọn.";
                continue;
            }

            $beforeStatus = $seat->trang_thai;
            $isStartNow = $thoiGianBatDau->isPast() || $thoiGianBatDau->isCurrentMinute();

            $lich = LichBaoTriGheNgoi::create([
                'ghe_ngoi_id' => $seat->id,
                'phong_chieu_id' => $phongChieu->id,
                'nguoi_dung_id' => auth()->id(),
                'thoi_gian_bat_dau' => $thoiGianBatDau,
                'thoi_gian_ket_thuc' => $thoiGianKetThuc,
                'ly_do' => $validated['ly_do'],
                'trang_thai_truoc' => $beforeStatus,
                'trang_thai_sau' => 'bao_tri',
                'trang_thai' => $isStartNow ? 'dang_thuc_hien' : 'cho_thuc_hien',
            ]);

            GheNgoi::where('id', $seat->id)->update(['trang_thai' => $isStartNow ? 'bao_tri' : 'sap_bao_tri']);

            $seat = GheNgoi::with('loaiGhe')->find($seat->id);

            $createdSchedules[] = [
                'ghe_id' => $seat->id,
                'ma_ghe' => $seat->ma_ghe,
                'lich_id' => $lich->id,
                'thoi_gian_bat_dau' => $lich->thoi_gian_bat_dau->format('d/m/Y H:i'),
                'thoi_gian_ket_thuc' => $lich->thoi_gian_ket_thuc ? $lich->thoi_gian_ket_thuc->format('d/m/Y H:i') : null,
            ];

            $updatedSeats[] = [
                'id' => $seat->id,
                'ma_ghe' => $seat->ma_ghe,
                'loai_ghe' => $seat->loaiGhe->ten_loai ?? 'Thường',
                'loai_ghe_id' => $seat->loai_ghe_id,
                'mau_sac' => $seat->loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $seat->loaiGhe->phu_thu ?? 0,
                'trang_thai' => $seat->trang_thai,
            ];
        }

        if (!empty($errors) && empty($createdSchedules)) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', $errors),
                'errors' => $errors,
            ], 422);
        }

        $success = count($createdSchedules) > 0;
        $message = $success 
            ? "Đã lên lịch bảo trì cho " . count($createdSchedules) . " ghế." 
            : "Không thể lên lịch bảo trì.";

        if ($success) {
            $maGhes = collect($createdSchedules)->pluck('ma_ghe')->join(', ');
            $this->ghiNhatKy(
                $request,
                'Lên lịch bảo trì ghế',
                'Quản lý ghế',
                "Phòng {$phongChieu->ten_phong}: Lên lịch bảo trì ghế [{$maGhes}] từ {$thoiGianBatDau->format('d/m/Y H:i')} " . ($thoiGianKetThuc ? "đến {$thoiGianKetThuc->format('d/m/Y H:i')}" : "") . " - {$validated['ly_do']}"
            );
        }

        return response()->json([
            'success' => $success,
            'message' => $message . (!empty($errors) ? ' (' . implode('; ', $errors) . ')' : ''),
            'schedules' => $createdSchedules,
            'updated_seats' => $updatedSeats,
            'errors' => $errors,
        ]);
    }

    public function scheduleRowMaintenance(Request $request, PhongChieu $phongChieu)
    {
        $validated = $request->validate([
            'hang_ghe_id' => 'required|exists:hang_ghes,id',
            'thoi_gian_bat_dau' => 'required|date',
            'thoi_gian_ket_thuc' => 'nullable|date|after:thoi_gian_bat_dau',
            'ly_do' => 'required|string|max:500',
        ], [
            'hang_ghe_id.required' => 'Vui lòng chọn hàng ghế cần bảo trì.',
            'thoi_gian_bat_dau.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'thoi_gian_ket_thuc.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'ly_do.required' => 'Vui lòng nhập lý do bảo trì.',
        ]);

        $hangGhe = $phongChieu->hangGhes()->findOrFail($validated['hang_ghe_id']);
        $ghes = $hangGhe->gheNgois;

        if ($ghes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Hàng {$hangGhe->ten_hang} không có ghế nào để bảo trì.",
            ], 422);
        }

        $request->merge([
            'ghe_ids' => $ghes->pluck('id')->toArray(),
        ]);

        return $this->scheduleSeatMaintenance($request, $phongChieu);
    }

    public function toggleRowMaintenance(Request $request, PhongChieu $phongChieu)
    {
        if ($request->filled('thoi_gian_bat_dau') || $request->filled('ngay_bat_dau')) {
            return $this->scheduleRowMaintenance($request, $phongChieu);
        }

        $request->validate([
            'hang_ghe_id' => 'required|exists:hang_ghes,id',
            'action' => 'nullable|in:maintenance,activate',
        ]);

        $hangGhe = $phongChieu->hangGhes()->findOrFail($request->hang_ghe_id);
        $ghes = $hangGhe->gheNgois()->get();

        if ($ghes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Hàng không có ghế nào.',
            ], 422);
        }

        $soBaoTri = $ghes->where('trang_thai', 'bao_tri')->count();
        $action = $request->action ?: ($soBaoTri > 0 ? 'activate' : 'maintenance');

        $service = app(SeatMaintenanceService::class);
        $removedScheduleIds = [];

        if ($action === 'maintenance') {
            $allCanMaintain = true;
            $conflictList = [];
            $gheIdsToMaintain = [];

            foreach ($ghes as $ghe) {
                if ($ghe->trang_thai !== 'hoat_dong' && $ghe->trang_thai !== 'sap_bao_tri') continue;

                $result = $service->canMaintainNow($ghe);
                if (!$result['can']) {
                    $allCanMaintain = false;
                    $conflictList = array_merge($conflictList, $result['conflicts']);
                } else {
                    $gheIdsToMaintain[] = $ghe->id;
                }
            }

            if (!$allCanMaintain) {
                $uniqueConflicts = collect($conflictList)->unique('ve_id')->count();
                return response()->json([
                    'success' => false,
                    'message' => "Không thể bảo trì hàng {$hangGhe->ten_hang}: Có {$uniqueConflicts} vé đã đặt ở các suất chiếu sắp tới.",
                    'conflicts_count' => $uniqueConflicts,
                ], 422);
            }

            foreach ($gheIdsToMaintain as $gheId) {
                $seat = $phongChieu->gheNgois()->findOrFail($gheId);
                $service->maintainNow($seat, auth()->id());
            }
        } else {
            foreach ($ghes as $ghe) {
                if ($ghe->trang_thai !== 'bao_tri') continue;

                $pending = LichBaoTriGheNgoi::where('ghe_ngoi_id', $ghe->id)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->latest()
                    ->first();
                if ($pending) {
                    $removedScheduleIds[] = $pending->id;
                    $service->completeMaintenance($pending, auth()->id());
                } else {
                    $ghe->update(['trang_thai' => 'hoat_dong']);
                }
            }
        }

        $ghes = $ghes->fresh();

        return response()->json([
            'success' => true,
            'message' => $action === 'maintenance'
                ? "Đã chuyển {$ghes->count()} ghế trong hàng {$hangGhe->ten_hang} sang bảo trì."
                : "Đã kích hoạt lại {$ghes->count()} ghế trong hàng {$hangGhe->ten_hang}.",
            'trang_thai' => $action === 'maintenance' ? 'bao_tri' : 'hoat_dong',
            'updated_seats' => $ghes->map(fn($g) => ['id' => $g->id, 'trang_thai' => $g->trang_thai])->toArray(),
            'removed_schedule_ids' => $removedScheduleIds,
            'expired_schedule_ids' => $removedScheduleIds,
        ]);
    }

    /**
     * API: Tự động kiểm tra khóa ghế đến giờ & mở ghế hết hạn
     */
    public function checkExpiredMaintenance(Request $request, PhongChieu $phongChieu)
    {
        $result = $this->processMaintenanceSchedules($phongChieu->id);

        return response()->json([
            'success' => true,
            'updated_seats' => $result['updated_seats'],
            'started_schedule_ids' => $result['started_schedule_ids'],
            'expired_schedule_ids' => $result['expired_schedule_ids'],
            'removed_schedule_ids' => $result['removed_schedule_ids'],
        ]);
    }

    /**
     * HELPER: Xử lý tự động khóa ghế khi đến giờ & mở ghế khi hết hạn
     */
    private function processMaintenanceSchedules($phongChieuId)
    {
        $now = now();
        $updatedSeats = [];
        $startedScheduleIds = [];
        $expiredScheduleIds = [];

        // 1. TỰ ĐỘNG KHÓA GHẾ KHI ĐẾN GIỜ BẢO TRÌ (cho_thuc_hien -> dang_thuc_hien)
        $lichsBatDau = LichBaoTriGheNgoi::where('phong_chieu_id', $phongChieuId)
            ->where('trang_thai', 'cho_thuc_hien')
            ->where('thoi_gian_bat_dau', '<=', $now)
            ->with('gheNgoi')
            ->get();

        foreach ($lichsBatDau as $lich) {
            $ghe = $lich->gheNgoi;
            if (!$ghe) continue;

            // Khóa ghế sang màu đỏ 'bao_tri'
            $ghe->update(['trang_thai' => 'bao_tri']);
            $lich->update(['trang_thai' => 'dang_thuc_hien']);

            $startedScheduleIds[] = $lich->id;

            $ghe->refresh();
            $ghe->load('loaiGhe');

            $updatedSeats[] = [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                'loai_ghe_id' => $ghe->loai_ghe_id,
                'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $ghe->loaiGhe->phu_thu ?? 0,
                'trang_thai' => $ghe->trang_thai,
            ];
        }

        // 2. TỰ ĐỘNG MỞ GHẾ KHI HẾT HẠN BẢO TRÌ (dang_thuc_hien -> da_hoan_thanh)
        $phongChieu = PhongChieu::find($phongChieuId);
        $lichsHetHan = LichBaoTriGheNgoi::where('phong_chieu_id', $phongChieuId)
            ->whereIn('trang_thai', ['dang_thuc_hien', 'cho_thuc_hien'])
            ->whereNotNull('thoi_gian_ket_thuc')
            ->where('thoi_gian_ket_thuc', '<=', $now)
            ->with('gheNgoi')
            ->get();

        foreach ($lichsHetHan as $lich) {
            $ghe = $lich->gheNgoi;
            if (!$ghe) continue;

            $hasBooking = VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
                ->where('ten_phong', $phongChieu->ten_phong ?? '')
                ->whereNotNull('ma_ghe')
                ->where('ma_ghe', '!=', '')
                ->get()
                ->filter(function ($ve) use ($ghe) {
                    $seats = array_filter(array_map('trim', explode(',', (string) $ve->ma_ghe)));
                    return in_array($ghe->ma_ghe, $seats, true);
                })
                ->isNotEmpty();

            if ($hasBooking) continue;

            $ghe->update(['trang_thai' => 'hoat_dong']);
            $lich->update(['trang_thai' => 'da_hoan_thanh']);

            $expiredScheduleIds[] = $lich->id;

            $ghe->refresh();
            $ghe->load('loaiGhe');

            $updatedSeats[] = [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                'loai_ghe_id' => $ghe->loai_ghe_id,
                'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $ghe->loaiGhe->phu_thu ?? 0,
                'trang_thai' => $ghe->trang_thai,
            ];
        }

        return [
            'updated_seats' => $updatedSeats,
            'started_schedule_ids' => $startedScheduleIds,
            'expired_schedule_ids' => $expiredScheduleIds,
            'removed_schedule_ids' => array_unique(array_merge($startedScheduleIds, $expiredScheduleIds)),
        ];
    }

    protected function hasConflictInPeriod($seat, $startTime, $endTime)
    {
        $phongChieu = $seat->phongChieu;
        if (!$phongChieu) return false;

        $query = VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where('ten_phong', $phongChieu->ten_phong)
            ->where('thoi_gian_chieu', '>=', $startTime);

        if ($endTime) {
            $query->where('thoi_gian_chieu', '<=', $endTime);
        }

        $ves = $query->get();

        foreach ($ves as $ve) {
            if (empty($ve->ma_ghe)) continue;
            $seats = array_filter(array_map('trim', explode(',', (string) $ve->ma_ghe)));
            if (in_array($seat->ma_ghe, $seats, true)) {
                return true;
            }
        }

        return false;
    }

    protected function validateSeatRowRules(LoaiGhe $loaiGhe, HangGhe $hangGhe): ?array
    {
        $hangGhes = $hangGhe->phongChieu->hangGhes()->orderBy('ten_hang')->get();
        $totalRows = $hangGhes->count();

        $currentIndex = $hangGhes->search(fn($h) => $h->id === $hangGhe->id);
        
        $isCouple = $loaiGhe->la_couple === true;
        $isVip = stripos($loaiGhe->ten_loai, 'vip') !== false;
        $isThuong = !$isVip && !$isCouple;
        
        $isFirstThreeRows = $currentIndex <= 2;
        $isLastTwoRows = $currentIndex >= $totalRows - 2;
        $isMiddleRow = !$isFirstThreeRows && !$isLastTwoRows;

        if ($isFirstThreeRows) {
            if ($isCouple) return ['3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế Couple.'];
            if ($isVip) return ['3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế VIP.'];
        }
        
        if ($isMiddleRow) {
            if ($isCouple) return ['Hàng ghế này chỉ được đặt ghế VIP. Không thể đặt ghế Couple.'];
            if ($isThuong) return ['Hàng ghế này chỉ được đặt ghế VIP. Không thể đặt ghế Thường.'];
        }

        if ($isLastTwoRows) {
            if ($isThuong) return ['2 hàng cuối chỉ được đặt ghế Couple. Không thể đặt ghế Thường.'];
            if ($isVip) return ['2 hàng cuối chỉ được đặt ghế Couple. Không thể đặt ghế VIP.'];
        }

        return null;
    }

    public function updateSeatType(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'ghe_id' => 'nullable|exists:ghe_ngois,id',
            'ghe_ids' => 'nullable|array',
            'ghe_ids.*' => 'exists:ghe_ngois,id',
            'loai_ghe_id' => 'required|exists:loai_ghes,id',
        ]);

        $gheIds = $request->ghe_ids ?? [$request->ghe_id];
        if (empty($gheIds)) {
            return response()->json(['success' => false, 'message' => 'Thiếu ID ghế.'], 422);
        }

        $loaiGhe = LoaiGhe::findOrFail($request->loai_ghe_id);

        $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('hangGhe')->get();
        foreach ($ghes as $ghe) {
            $errors = $this->validateSeatRowRules($loaiGhe, $ghe->hangGhe);
            if ($errors !== null) {
                return response()->json([
                    'success' => false,
                    'message' => $errors[0],
                    'errors' => $errors,
                ], 422);
            }
        }

        $phongChieu->gheNgois()->whereIn('id', $gheIds)->update(['loai_ghe_id' => $loaiGhe->id]);

        $updatedSeats = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'loai_ghe' => $loaiGhe->ten_loai,
                'loai_ghe_id' => $loaiGhe->id,
                'mau_sac' => $loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $loaiGhe->phu_thu,
                'trang_thai' => $g->trang_thai,
            ])->toArray();

        return response()->json([
            'success' => true,
            'message' => "Đã đổi " . count($gheIds) . " ghế sang loại {$loaiGhe->ten_loai}.",
            'loai_ghe' => $loaiGhe->ten_loai,
            'loai_ghe_id' => $loaiGhe->id,
            'mau_sac' => $loaiGhe->mau_sac ?? '#666666',
            'phu_thu' => $loaiGhe->phu_thu,
            'updated_seats' => $updatedSeats,
        ]);
    }

    public function updateRowSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'hang_ghe_id' => 'required|exists:hang_ghes,id',
            'loai_ghe_id' => 'required|exists:loai_ghes,id',
        ]);

        $hangGhe = $phongChieu->hangGhes()->findOrFail($request->hang_ghe_id);
        $loaiGhe = LoaiGhe::findOrFail($request->loai_ghe_id);

        $errors = $this->validateSeatRowRules($loaiGhe, $hangGhe);
        if ($errors !== null) {
            return response()->json([
                'success' => false,
                'message' => $errors[0],
                'errors' => $errors,
            ], 422);
        }

        $ghes = $hangGhe->gheNgois()->with('loaiGhe')->get();

        $hangGhe->gheNgois()->update([
            'loai_ghe_id' => $loaiGhe->id,
        ]);

        $updatedSeats = $ghes->map(function ($g) use ($loaiGhe) {
            return [
                'id' => $g->id,
                'loai_ghe' => $loaiGhe->ten_loai,
                'loai_ghe_id' => $loaiGhe->id,
                'mau_sac' => $loaiGhe->mau_sac ?? '#666666',
                'phu_thu' => $loaiGhe->phu_thu,
                'trang_thai' => $g->trang_thai,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật {$ghes->count()} ghế trong hàng {$hangGhe->ten_hang} thành loại {$loaiGhe->ten_loai}.",
            'updated_seats' => $updatedSeats->toArray(),
        ]);
    }

    public function bulkUpdateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'ghe_ids' => 'required|array',
            'ghe_ids.*' => 'exists:ghe_ngois,id',
            'action' => 'required|in:update_type,toggle_maintenance,delete,maintenance,activate',
            'loai_ghe_id' => 'nullable|exists:loai_ghes,id',
        ]);

        $gheIds = $request->ghe_ids;
        $action = $request->action;
        $updatedSeats = [];
        $removedScheduleIds = [];

        if ($action === 'update_type') {
            $loaiGhe = LoaiGhe::findOrFail($request->loai_ghe_id);
            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with(['loaiGhe', 'hangGhe'])->get();

            foreach ($ghes as $g) {
                $errors = $this->validateSeatRowRules($loaiGhe, $g->hangGhe);
                if ($errors !== null) {
                    return response()->json([
                        'success' => false,
                        'message' => $errors[0],
                        'errors' => $errors,
                    ], 422);
                }
            }

            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->update(['loai_ghe_id' => $loaiGhe->id]);

            $this->ghiNhatKy(
                $request,
                'Cập nhật loại ghế',
                'Quản lý ghế',
                "Phòng {$phongChieu->ten_phong}: Cập nhật " . count($ghes) . " ghế sang loại [{$loaiGhe->ten_loai}]"
            );

            foreach ($ghes as $g) {
                $updatedSeats[] = [
                    'id' => $g->id,
                    'loai_ghe' => $loaiGhe->ten_loai,
                    'loai_ghe_id' => $loaiGhe->id,
                    'mau_sac' => $loaiGhe->mau_sac ?? '#666666',
                    'phu_thu' => $loaiGhe->phu_thu,
                    'trang_thai' => $g->trang_thai,
                ];
            }
        } elseif ($action === 'toggle_maintenance') {
            $firstGhe = $phongChieu->gheNgois()->whereIn('id', $gheIds)->first();
            $currentStatus = $firstGhe->trang_thai ?? 'hoat_dong';
            $newStatus = $currentStatus === 'bao_tri' ? 'hoat_dong' : 'bao_tri';
            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get();

            if ($newStatus === 'hoat_dong') {
                $schedulesToCancel = LichBaoTriGheNgoi::whereIn('ghe_ngoi_id', $gheIds)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->get();
                $removedScheduleIds = $schedulesToCancel->pluck('id')->toArray();

                LichBaoTriGheNgoi::whereIn('ghe_ngoi_id', $gheIds)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->update([
                        'trang_thai' => 'da_huy',
                        'ghi_chu' => 'Đã kích hoạt lại sớm.'
                    ]);
            }

            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->update(['trang_thai' => $newStatus]);

            $maGhes = $ghes->pluck('ma_ghe')->join(', ');
            $this->ghiNhatKy(
                $request,
                $newStatus === 'bao_tri' ? 'Khóa ghế' : 'Mở khóa ghế',
                'Quản lý ghế',
                "Phòng {$phongChieu->ten_phong}: " . ($newStatus === 'bao_tri' ? 'Khóa' : 'Mở khóa') . " ghế [{$maGhes}]"
            );

            foreach ($ghes as $g) {
                $updatedSeats[] = [
                    'id' => $g->id,
                    'loai_ghe' => $g->loaiGhe->ten_loai ?? 'Thường',
                    'loai_ghe_id' => $g->loai_ghe_id,
                    'mau_sac' => $g->loaiGhe->mau_sac ?? '#666666',
                    'phu_thu' => $g->loaiGhe->phu_thu ?? 0,
                    'trang_thai' => $newStatus,
                ];
            }
        } elseif ($action === 'maintenance' || $action === 'activate') {
            $newStatus = $action === 'maintenance' ? 'bao_tri' : 'hoat_dong';
            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get();

            if ($action === 'activate') {
                $phongChieu->gheNgois()
                    ->whereIn('id', $gheIds)
                    ->update(['trang_thai' => 'hoat_dong']);

                $schedulesToCancel = LichBaoTriGheNgoi::whereIn('ghe_ngoi_id', $gheIds)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->get();

                $removedScheduleIds = $schedulesToCancel->pluck('id')->toArray();

                LichBaoTriGheNgoi::whereIn('ghe_ngoi_id', $gheIds)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->update([
                        'trang_thai' => 'da_huy',
                        'ghi_chu' => 'Đã kích hoạt lại sớm.'
                    ]);
            } else {
                $phongChieu->gheNgois()
                    ->whereIn('id', $gheIds)
                    ->update(['trang_thai' => $newStatus]);
            }

            $maGhes = $ghes->pluck('ma_ghe')->join(', ');
            $this->ghiNhatKy(
                $request,
                $action === 'maintenance' ? 'Bảo trì ghế' : 'Kích hoạt ghế',
                'Quản lý ghế',
                "Phòng {$phongChieu->ten_phong}: " . ($action === 'maintenance' ? 'Chuyển sang bảo trì' : 'Kích hoạt') . " ghế [{$maGhes}]"
            );

            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get();

            foreach ($ghes as $g) {
                $updatedSeats[] = [
                    'id' => $g->id,
                    'loai_ghe' => $g->loaiGhe->ten_loai ?? 'Thường',
                    'loai_ghe_id' => $g->loai_ghe_id,
                    'mau_sac' => $g->loaiGhe->mau_sac ?? '#666666',
                    'phu_thu' => $g->loaiGhe->phu_thu ?? 0,
                    'trang_thai' => $g->trang_thai,
                ];
            }
        } elseif ($action === 'delete') {
            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->get();
            $maGheList = $ghes->pluck('ma_ghe')->all();
            $conflicted = $this->findSeatsInUse($phongChieu, $maGheList);
            if (!empty($conflicted)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa: các ghế [' . implode(', ', array_slice($conflicted, 0, 10))
                        . (count($conflicted) > 10 ? '...' : '')
                        . '] đang có vé đã bán/đã sử dụng. Hãy hủy vé trước khi xóa.',
                ], 422);
            }

            $coupleGroupIds = $ghes->whereNotNull('couple_group_id')->pluck('couple_group_id')->unique()->all();
            $soGheBiXoa = count($gheIds);

            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->delete();

            $this->ghiNhatKy(
                $request,
                'Xóa ghế',
                'Quản lý ghế',
                "Phòng {$phongChieu->ten_phong}: Xóa {$soGheBiXoa} ghế [" . implode(', ', array_slice($maGheList, 0, 10)) . (count($maGheList) > 10 ? ',...' : '') . "]"
            );

            foreach ($coupleGroupIds as $gid) {
                GheNgoi::where('couple_group_id', $gid)
                    ->update(['couple_group_id' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$soGheBiXoa} ghế.",
                'updated_seats' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật " . count($updatedSeats) . " ghế.",
            'updated_seats' => $updatedSeats,
            'removed_schedule_ids' => $removedScheduleIds,
            'expired_schedule_ids' => $removedScheduleIds,
        ]);
    }

    public function findSeatsInUsePublic(PhongChieu $phongChieu, array $maGheCanKiemTra): array
    {
        return $this->findSeatsInUse($phongChieu, $maGheCanKiemTra);
    }

    protected function findSeatsInUse(PhongChieu $phongChieu, array $maGheCanKiemTra): array
    {
        if (empty($maGheCanKiemTra)) return [];

        $maGheCanKiemTra = array_values(array_filter(array_map('trim', $maGheCanKiemTra)));
        if (empty($maGheCanKiemTra)) return [];

        $tenPhong = $phongChieu->ten_phong;
        $tenRap = optional($phongChieu->rapChieuPhim)->ten_rap;

        $escaped = array_map(fn ($m) => preg_quote($m, '/'), $maGheCanKiemTra);
        $pattern = '/(?:^|,)(?:' . implode('|', $escaped) . ')(?:,|$)/';

        $maGheTrongVe = VeXemPhim::query()
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->whereNotNull('ma_ghe')
            ->where('ma_ghe', '!=', '')
            ->where(function ($q) use ($tenPhong, $tenRap) {
                $q->where('ten_phong', $tenPhong);
                if (!empty($tenRap)) {
                    $q->where('ten_rap', $tenRap);
                }
            })
            ->pluck('ma_ghe')
            ->all();

        $conflicted = [];
        foreach ($maGheTrongVe as $csv) {
            if (preg_match($pattern, $csv)) {
                foreach ($maGheCanKiemTra as $ma) {
                    if (preg_match('/(?:^|,)' . preg_quote($ma, '/') . '(?:,|$)/', $csv)) {
                        $conflicted[$ma] = true;
                    }
                }
            }
        }

        return array_keys($conflicted);
    }

    public function createSeat(Request $request, PhongChieu $phongChieu)
    {
        $data = $request->validate([
            'hang_ghe_id' => ['required', 'integer', function ($attr, $value, $fail) use ($phongChieu) {
                $exists = $phongChieu->hangGhes()->where('id', $value)->exists();
                if (!$exists) $fail('Hàng ghế không thuộc phòng chiếu này.');
            }],
            'ma_ghe' => [
                'required', 'string', 'max:10',
                function ($attr, $value, $fail) use ($phongChieu) {
                    $exists = GheNgoi::where('phong_chieu_id', $phongChieu->id)
                        ->where('ma_ghe', $value)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) $fail("Mã ghế {$value} đã tồn tại trong phòng chiếu này.");
                },
            ],
            'cot' => [
                'required', 'integer', 'min:1',
                function ($attr, $value, $fail) use ($request) {
                    $exists = GheNgoi::where('hang_ghe_id', $request->hang_ghe_id)
                        ->where('cot', $value)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) $fail("Cột {$value} đã có ghế trong hàng được chọn.");
                },
            ],
            'loai_ghe_id' => ['required', 'exists:loai_ghes,id'],
            'trang_thai' => ['required', 'in:hoat_dong,bao_tri'],
        ]);

        $loaiGhe = LoaiGhe::findOrFail($data['loai_ghe_id']);
        $hangGhe = HangGhe::findOrFail($data['hang_ghe_id']);

        $errors = $this->validateSeatRowRules($loaiGhe, $hangGhe);
        if ($errors !== null) {
            return response()->json([
                'success' => false,
                'message' => $errors[0],
                'errors' => $errors,
            ], 422);
        }

        try {
            $ghe = DB::transaction(function () use ($phongChieu, $data) {
                GheNgoi::onlyTrashed()
                    ->where('phong_chieu_id', $phongChieu->id)
                    ->where('ma_ghe', $data['ma_ghe'])
                    ->forceDelete();
                GheNgoi::onlyTrashed()
                    ->where('hang_ghe_id', $data['hang_ghe_id'])
                    ->where('cot', $data['cot'])
                    ->forceDelete();

                $ghe = GheNgoi::create([
                    'phong_chieu_id' => $phongChieu->id,
                    'hang_ghe_id' => $data['hang_ghe_id'],
                    'loai_ghe_id' => $data['loai_ghe_id'],
                    'ma_ghe' => $data['ma_ghe'],
                    'cot' => $data['cot'],
                    'trang_thai' => $data['trang_thai'],
                ]);

                $this->seatGenerator->attachCoupleGroupForSeat($ghe);

                $phongChieu->update([
                    'suc_chua' => $phongChieu->gheNgois()->count() + 1,
                ]);

                return $ghe;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ghế hoặc cột bị trùng với bản ghi đã xóa trước đó. Vui lòng thử lại.',
            ], 409);
        }

        $ghe->load(['hangGhe', 'loaiGhe']);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm ghế {$ghe->ma_ghe}.",
            'seat' => [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'cot' => $ghe->cot,
                'trang_thai' => $ghe->trang_thai,
                'loai_ghe_id' => $ghe->loai_ghe_id,
                'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#666',
                'phu_thu' => (float) ($ghe->loaiGhe->phu_thu ?? 0),
                'hang_ghe_id' => $ghe->hang_ghe_id,
                'hang_ten' => $ghe->hangGhe->ten_hang ?? '',
            ],
        ]);
    }

    public function createRow(Request $request, PhongChieu $phongChieu)
    {
        $data = $request->validate([
            'ten_hang' => [
                'required', 'string', 'max:10',
                function ($attr, $value, $fail) use ($phongChieu) {
                    $exists = HangGhe::where('phong_chieu_id', $phongChieu->id)
                        ->where('ten_hang', $value)
                        ->exists();
                    if ($exists) $fail("Tên hàng {$value} đã tồn tại trong phòng chiếu này.");
                },
            ],
            'la_hang_couple' => ['nullable', 'boolean'],
            'loai_ghe_mac_dinh_id' => ['nullable', 'exists:loai_ghes,id'],
            'tu_dong_tao_ghe' => ['nullable', 'boolean'],
            'so_ghe' => ['nullable', 'integer', 'min:1', 'max:50'],
            'cot_bat_dau' => ['nullable', 'integer', 'min:1'],
            'loai_ghe_id' => ['nullable', 'exists:loai_ghes,id'],
            'trang_thai' => ['nullable', 'in:hoat_dong,bao_tri'],
        ]);

        $laHangCouple = $request->boolean('la_hang_couple');
        $tuDongTaoGhe = $request->boolean('tu_dong_tao_ghe');
        $soGhe = (int) ($data['so_ghe'] ?? 0);
        $cotBatDau = (int) ($data['cot_bat_dau'] ?? 1);
        $loaiGheId = $data['loai_ghe_id'] ?? $data['loai_ghe_mac_dinh_id'] ?? null;
        $trangThai = $data['trang_thai'] ?? 'hoat_dong';

        $existingHangs = $phongChieu->hangGhes()->orderBy('ten_hang')->get();
        $newTenHang = $data['ten_hang'];
        $allHangs = $existingHangs->push((object)['id' => 'new', 'ten_hang' => $newTenHang])
            ->sortBy('ten_hang')->values();
        $newHangIndex = $allHangs->search(fn($h) => $h->ten_hang === $newTenHang);
        $totalRows = $allHangs->count();

        $firstThreeIndices = range(0, 2);
        $lastThreeIndices = range(max(0, $totalRows - 3), $totalRows - 1);

        $isFirstThree = in_array($newHangIndex, $firstThreeIndices);
        $isLastThree = in_array($newHangIndex, $lastThreeIndices);

        if ($loaiGheId) {
            $loaiGhe = LoaiGhe::find($loaiGheId);
            if ($loaiGhe) {
                $isVip = strtolower($loaiGhe->ten_loai) === 'vip';
                $isCouple = $loaiGhe->la_couple === true;
                $isThuong = strtolower($loaiGhe->ten_loai) === 'thường' || strtolower($loaiGhe->ten_loai) === 'thuong';

                if ($isFirstThree && ($isVip || $isCouple)) {
                    return response()->json([
                        'success' => false,
                        'message' => $isVip ? '3 hàng gần màn chiếu không được đặt ghế VIP.' : '3 hàng gần màn chiếu không được đặt ghế Couple.',
                    ], 422);
                }

                if ($isLastThree && $isThuong) {
                    return response()->json([
                        'success' => false,
                        'message' => '3 hàng cuối phòng không được đặt ghế thường. Chỉ được đặt ghế VIP hoặc Couple.',
                    ], 422);
                }
            }
        }

        $seatsToCreate = [];
        if ($tuDongTaoGhe && $soGhe > 0) {
            for ($i = 0; $i < $soGhe; $i++) {
                $seatsToCreate[] = [
                    'ma_ghe' => $data['ten_hang'] . ($cotBatDau + $i),
                    'cot' => $cotBatDau + $i,
                ];
            }
        }

        try {
            $result = DB::transaction(function () use (
                $phongChieu, $data, $laHangCouple, $tuDongTaoGhe,
                $soGhe, $cotBatDau, $loaiGheId, $trangThai, $seatsToCreate
            ) {
                if (!empty($seatsToCreate)) {
                    $maGheList = array_column($seatsToCreate, 'ma_ghe');
                    GheNgoi::onlyTrashed()
                        ->where('phong_chieu_id', $phongChieu->id)
                        ->whereIn('ma_ghe', $maGheList)
                        ->forceDelete();
                }

                $hangGhe = HangGhe::create([
                    'phong_chieu_id' => $phongChieu->id,
                    'ten_hang' => $data['ten_hang'],
                    'la_hang_couple' => $laHangCouple,
                    'loai_ghe_mac_dinh_id' => $data['loai_ghe_mac_dinh_id'] ?? null,
                ]);

                if (!empty($seatsToCreate)) {
                    $cotList = array_column($seatsToCreate, 'cot');
                    GheNgoi::onlyTrashed()
                        ->where('hang_ghe_id', $hangGhe->id)
                        ->whereIn('cot', $cotList)
                        ->forceDelete();
                }

                $seats = [];
                if ($tuDongTaoGhe && $soGhe > 0 && $loaiGheId) {
                    for ($i = 0; $i < $soGhe; $i++) {
                        $cot = $cotBatDau + $i;
                        $ghe = GheNgoi::create([
                            'phong_chieu_id' => $phongChieu->id,
                            'hang_ghe_id' => $hangGhe->id,
                            'loai_ghe_id' => $loaiGheId,
                            'ma_ghe' => $hangGhe->ten_hang . $cot,
                            'cot' => $cot,
                            'trang_thai' => $trangThai,
                        ]);

                        if ($laHangCouple && $i % 2 === 1) {
                            $gheTruoc = $seats[$i - 1] ?? null;
                            $groupId = sprintf('CPL_P%d_H%d_R%d', $phongChieu->id, $hangGhe->id, (int) ($i / 2));
                            $ghe->couple_group_id = $groupId;
                            $ghe->save();
                            if ($gheTruoc && !$gheTruoc->couple_group_id) {
                                $gheTruoc->couple_group_id = $groupId;
                                $gheTruoc->save();
                            }
                        }

                        $seats[] = $ghe;
                    }
                }

                $phongChieu->update([
                    'suc_chua' => $phongChieu->gheNgois()->count(),
                ]);

                return [
                    'hangGhe' => $hangGhe,
                    'seats' => collect($seats),
                ];
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ghế hoặc cột bị trùng với bản ghi đã xóa trước đó. Vui lòng thử lại.',
            ], 409);
        }

        $result['hangGhe']->load('gheNgois.loaiGhe');

        return response()->json([
            'success' => true,
            'message' => $tuDongTaoGhe && $soGhe > 0
                ? "Đã thêm hàng {$result['hangGhe']->ten_hang} và {$soGhe} ghế."
                : "Đã thêm hàng {$result['hangGhe']->ten_hang}.",
            'row' => [
                'id' => $result['hangGhe']->id,
                'ten_hang' => $result['hangGhe']->ten_hang,
                'la_hang_couple' => (bool) $result['hangGhe']->la_hang_couple,
                'so_ghe' => $result['hangGhe']->gheNgois->count(),
            ],
            'seats' => $result['seats']->map(fn ($g) => [
                'id' => $g->id,
                'ma_ghe' => $g->ma_ghe,
                'cot' => $g->cot,
                'trang_thai' => $g->trang_thai,
                'loai_ghe_id' => $g->loai_ghe_id,
            ])->values(),
        ]);
    }

    public function deleteRowSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'hang_ghe_id' => 'required|exists:hang_ghes,id',
        ]);

        $hangGhe = $phongChieu->hangGhes()->findOrFail($request->hang_ghe_id);

        $ghes = $hangGhe->gheNgois()->get();
        if ($ghes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Hàng không có ghế nào.',
            ], 422);
        }

        $maGheList = $ghes->pluck('ma_ghe')->all();
        $conflicted = $this->findSeatsInUse($phongChieu, $maGheList);
        if (!empty($conflicted)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa: các ghế [' . implode(', ', array_slice($conflicted, 0, 10))
                    . (count($conflicted) > 10 ? '...' : '')
                    . '] đang có vé đã bán/đã sử dụng. Hãy hủy vé trước khi xóa.',
            ], 422);
        }

        $deleted = $hangGhe->gheNgois()->delete();
        $hangGhe->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa hàng {$hangGhe->ten_hang} và {$deleted} ghế.",
        ]);
    }

    protected function ghiNhatKy(Request $request, string $hanhDong, string $chucNang, string $moTa): void
    {
        try {
            \App\Models\NhatKyHoatDongHeThong::create([
                'nguoi_dung_id' => auth()->id(),
                'hanh_dong' => $hanhDong,
                'chuc_nang' => $chucNang,
                'mo_ta' => $moTa,
                'dia_chi_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silence exception
        }
    }
}