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
use App\Models\VeXemPhim;
use App\Services\SeatGeneratorService;
use App\Services\SeatMaintenanceService;
use App\Traits\Loggable;
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

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $rapChieuPhims = RapChieuPhim::where('trang_thai', 'hoat_dong')
            ->orderBy('ten_rap')
            ->get();

        return view('admin.phong-chieus.create', compact('rapChieuPhims'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        $phongChieu->load(['rapChieuPhim', 'hangGhes.gheNgois.loaiGhe', 'suatChieus.phim']);

        $seatMap = $this->seatGenerator->getSeatMap($phongChieu);
        $soHang = $phongChieu->hangGhes->count();
        $soCot = $phongChieu->gheNgois->count() > 0 
            ? $phongChieu->gheNgois->max('cot') 
            : 0;

        return view('admin.phong-chieus.show', compact(
            'phongChieu',
            'seatMap',
            'soHang',
            'soCot'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhongChieu $phongChieu): View
    {
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        return view('admin.phong-chieus.edit', compact('phongChieu', 'rapChieuPhims'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhongChieuRequest $request, PhongChieu $phongChieu)
    {
        $data = $request->validated();
        
        $phongChieu->update($data);

        $this->ghiNhatKy($request, 'Cập nhật phòng chiếu', 'Quản lý phòng & ghế', "Cập nhật phòng: {$phongChieu->ten_phong}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Force delete the specified resource.
     */
    public function forceDestroy(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->forceDelete();

        $this->ghiNhatKy($request, 'Xóa vĩnh viễn phòng chiếu', 'Quản lý phòng & ghế', "Xóa vĩnh viễn phòng #{$id}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa vĩnh viễn.');
    }

    /**
     * Restore the specified resource.
     */
    public function restore(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->restore();

        $this->ghiNhatKy($request, 'Khôi phục phòng chiếu', 'Quản lý phòng & ghế', "Khôi phục phòng #{$id}");

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được khôi phục.');
    }

    /**
     * Generate seats for the room.
     */
    public function generateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'so_hang' => 'required|integer|min:1|max:20',
            'so_cot' => 'required|integer|min:1|max:20',
            'loai_ghe_thuong_id' => 'required|exists:loai_ghes,id',
            'loai_ghe_vip_id' => 'nullable|exists:loai_ghes,id',
            'loai_ghe_couple_id' => 'nullable|exists:loai_ghes,id',
        ]);

        try {
            $ketQua = $this->seatGenerator->generateSeats(
                $phongChieu,
                (int) $request->so_hang,
                (int) $request->so_cot,
                (int) $request->loai_ghe_thuong_id,
                $request->loai_ghe_vip_id,
                $request->loai_ghe_couple_id,
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

    /**
     * Toggle maintenance status for a single seat (AJAX).
     */
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

        if ($isMaintenance) {
            $allConflictList = [];
            $gheIdsToMaintain = [];

            foreach ($gheIds as $gheId) {
                $seat = $phongChieu->gheNgois()->findOrFail($gheId);
                if ($seat->trang_thai !== 'hoat_dong') {
                    continue;
                }
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
                if ($seat->trang_thai !== 'bao_tri') {
                    continue;
                }
                $pending = LichBaoTriGheNgoi::where('ghe_ngoi_id', $seat->id)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->latest()
                    ->first();
                if ($pending) {
                    $service->completeMaintenance($pending, auth()->id());
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
        ]);
    }

    /**
     * Update seat type for a single seat (AJAX).
     */
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

    /**
     * Update all seats in a row (AJAX).
     */
    public function updateRowSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'hang_ghe_id' => 'required|exists:hang_ghes,id',
            'loai_ghe_id' => 'required|exists:loai_ghes,id',
        ]);

        $hangGhe = $phongChieu->hangGhes()->findOrFail($request->hang_ghe_id);
        $loaiGhe = LoaiGhe::findOrFail($request->loai_ghe_id);

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

    /**
     * Bulk update seats (AJAX).
     */
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

        if ($action === 'update_type') {
            $loaiGhe = LoaiGhe::findOrFail($request->loai_ghe_id);
            $ghes = $phongChieu->gheNgois()->whereIn('id', $gheIds)->with('loaiGhe')->get();
            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->update(['loai_ghe_id' => $loaiGhe->id]);

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
            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->update(['trang_thai' => $newStatus]);

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
            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->update(['trang_thai' => $newStatus]);

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
        } elseif ($action === 'delete') {
            // Check ghế có vé đang dùng không
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

            // Gom các couple_group_id bị ảnh hưởng để dọn ghế "mồ côi"
            $coupleGroupIds = $ghes->whereNotNull('couple_group_id')->pluck('couple_group_id')->unique()->all();
            $soGheBiXoa = count($gheIds);

            $phongChieu->gheNgois()
                ->whereIn('id', $gheIds)
                ->delete();

            // Với mỗi couple_group_id bị xóa 1 (hoặc vài) ghế, dọn couple_group_id của ghế còn lại
            foreach ($coupleGroupIds as $gid) {
                \App\Models\GheNgoi::where('couple_group_id', $gid)
                    ->update(['couple_group_id' => null]);
            }

            $updatedSeats = []; // bulk delete không cần trả updated
            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$soGheBiXoa} ghế.",
                'updated_seats' => $updatedSeats,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật " . count($updatedSeats) . " ghế.",
            'updated_seats' => $updatedSeats,
        ]);
    }

    /**
     * Public wrapper cho findSeatsInUse (để GheNgoiController / HangGheController dùng chung).
     */
    public function findSeatsInUsePublic(PhongChieu $phongChieu, array $maGheCanKiemTra): array
    {
        return $this->findSeatsInUse($phongChieu, $maGheCanKiemTra);
    }

    /**
     * Kiểm tra danh sách ghế có vé đang sử dụng (đã thanh toán/đã dùng) ở phòng này không.
     * Trả về mảng ma_ghe bị cản (rỗng = ok).
     *
     * Lưu ý về cấu trúc DB:
     *  - Bảng `ve_xem_phims` không có cột `suat_chieu_id` (mặc dù migration có khai báo).
     *  - Để xác định vé thuộc phòng nào, ta lọc theo `ten_phong` (varchar trong vé)
     *    khớp với `phong_chieus.ten_phong` (kèm `ten_rap` để tránh trùng tên phòng ở 2 rạp).
     *  - Cột `ma_ghe` lưu CSV các ghế (VD: "A1,A2,A3"). Match chính xác bằng regex word-boundary.
     *
     * Tối ưu: 1 query duy nhất, xử lý bằng PHP thay vì N+1 LIKE.
     */
    protected function findSeatsInUse(PhongChieu $phongChieu, array $maGheCanKiemTra): array
    {
        if (empty($maGheCanKiemTra)) return [];

        $maGheCanKiemTra = array_values(array_filter(array_map('trim', $maGheCanKiemTra)));
        if (empty($maGheCanKiemTra)) return [];

        // Lấy ten_phong + ten_rap để lọc chính xác vé của phòng này
        // (tránh nhầm khi 2 rạp cùng tên phòng "Phòng 1")
        $tenPhong = $phongChieu->ten_phong;
        $tenRap = optional($phongChieu->rapChieuPhim)->ten_rap;

        // Xây regex: mã ghế phải là 1 phần tử trong CSV (word-boundary = (?:^|,)/(?:,|$))
        $escaped = array_map(fn ($m) => preg_quote($m, '/'), $maGheCanKiemTra);
        $pattern = '/(?:^|,)(?:' . implode('|', $escaped) . ')(?:,|$)/';

        // 1 query duy nhất: lấy ma_ghe của các vé thuộc phòng này
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

    /**
     * Thêm 1 ghế mới vào phòng chiếu (AJAX từ sơ đồ ghế).
     *
     * Request JSON: { hang_ghe_id, ma_ghe, cot, loai_ghe_id, trang_thai }
     * Response JSON: { success, message, seat: { ... } }
     *
     * Validate:
     *  - hang_ghe_id phải thuộc phòng chiếu hiện tại
     *  - ma_ghe duy nhất trong phòng (không tính soft-deleted)
     *  - cot duy nhất trong hàng (không tính soft-deleted)
     *
     * Lưu ý: Trước khi insert, tự động force-delete các bản ghi soft-deleted
     * có cùng (phong_chieu_id, ma_ghe) hoặc cùng (hang_ghe_id, cot) để tránh
     * lỗi unique constraint của MySQL (MySQL unique index không phân biệt
     * soft-deleted rows).
     */
    public function createSeat(Request $request, PhongChieu $phongChieu)
    {
        $data = $request->validate([
            'hang_ghe_id' => ['required', 'integer', function ($attr, $value, $fail) use ($phongChieu) {
                $exists = $phongChieu->hangGhes()->where('id', $value)->exists();
                if (!$exists) $fail('Hàng ghế không thuộc phòng chiếu này.');
            }],
            'ma_ghe' => [
                'required', 'string', 'max:10',
                // Unique trong phòng, bỏ qua soft-deleted
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
                // Unique trong hàng, bỏ qua soft-deleted
                function ($attr, $value, $fail) use ($request, $phongChieu) {
                    $exists = GheNgoi::where('hang_ghe_id', $request->hang_ghe_id)
                        ->where('cot', $value)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) $fail("Cột {$value} đã có ghế trong hàng được chọn.");
                },
            ],
            'loai_ghe_id' => ['required', 'exists:loai_ghes,id'],
            'trang_thai' => ['required', 'in:hoat_dong,bao_tri'],
        ], [
            'hang_ghe_id.required' => 'Vui lòng chọn hàng ghế.',
            'ma_ghe.required' => 'Vui lòng nhập mã ghế.',
            'ma_ghe.max' => 'Mã ghế tối đa 10 ký tự.',
            'cot.required' => 'Vui lòng nhập cột.',
            'cot.integer' => 'Cột phải là số nguyên.',
            'cot.min' => 'Cột phải lớn hơn hoặc bằng 1.',
            'loai_ghe_id.required' => 'Vui lòng chọn loại ghế.',
            'loai_ghe_id.exists' => 'Loại ghế không hợp lệ.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.',
        ]);

        try {
            $ghe = DB::transaction(function () use ($phongChieu, $data) {
                // Dọn dẹp các bản ghi soft-deleted cùng key trước khi insert
                // (MySQL unique index không phân biệt deleted_at)
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

                // Tự gán couple_group_id nếu thuộc loại ghế couple
                $this->seatGenerator->attachCoupleGroupForSeat($ghe);

                // Cập nhật suc_chua phòng
                $phongChieu->update([
                    'suc_chua' => $phongChieu->gheNgois()->count() + 1,
                ]);

                return $ghe;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ghế hoặc cột bị trùng với bản ghi đã xóa trước đó. Vui lòng thử lại hoặc F5 để tải lại trang.',
                'errors' => ['_global' => ['Dữ liệu bị xung đột với bản ghi đã xóa mềm. Hệ thống đã tự dọn dẹp, bạn có thể thử lại ngay.']],
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

    /**
     * Thêm 1 hàng ghế mới (có thể kèm nhiều ghế sinh tự động) vào phòng chiếu (AJAX từ sơ đồ ghế).
     *
     * Request JSON: {
     *   ten_hang, la_hang_couple (0/1), loai_ghe_mac_dinh_id,
     *   tu_dong_tao_ghe (0/1),
     *   so_ghe (số ghế tạo nếu tu_dong_tao_ghe=1), cot_bat_dau, loai_ghe_id (cho ghế nếu tu_dong_tao_ghe=1)
     * }
     *
     * Response JSON: { success, message, row: { id, ten_hang, la_hang_couple, so_ghe }, seats: [...] }
     */
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
        ], [
            'ten_hang.required' => 'Vui lòng nhập tên hàng.',
            'ten_hang.max' => 'Tên hàng tối đa 10 ký tự.',
            'so_ghe.min' => 'Số ghế phải lớn hơn 0.',
            'so_ghe.max' => 'Số ghế tối đa 50.',
        ]);

        $laHangCouple = $request->boolean('la_hang_couple');
        $tuDongTaoGhe = $request->boolean('tu_dong_tao_ghe');
        $soGhe = (int) ($data['so_ghe'] ?? 0);
        $cotBatDau = (int) ($data['cot_bat_dau'] ?? 1);
        $loaiGheId = $data['loai_ghe_id'] ?? $data['loai_ghe_mac_dinh_id'] ?? null;
        $trangThai = $data['trang_thai'] ?? 'hoat_dong';

        // Tính sẵn danh sách (ma_ghe, cot) sẽ được tạo (nếu auto)
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
                // Dọn dẹp các bản ghi soft-deleted cùng (ma_ghe) hoặc (hang_ghe_id+cot) trước khi insert
                // (MySQL unique index không phân biệt deleted_at)
                if (!empty($seatsToCreate)) {
                    $maGheList = array_column($seatsToCreate, 'ma_ghe');
                    $cotList = array_column($seatsToCreate, 'cot');
                    GheNgoi::onlyTrashed()
                        ->where('phong_chieu_id', $phongChieu->id)
                        ->whereIn('ma_ghe', $maGheList)
                        ->forceDelete();
                    // Lưu ý: chưa có hang_ghe_id mới nên chỉ xóa theo ma_ghe
                    // (cột trùng giữa các hàng là bình thường: A1 và B1 cùng cot=1)
                }

                $hangGhe = HangGhe::create([
                    'phong_chieu_id' => $phongChieu->id,
                    'ten_hang' => $data['ten_hang'],
                    'la_hang_couple' => $laHangCouple,
                    'loai_ghe_mac_dinh_id' => $data['loai_ghe_mac_dinh_id'] ?? null,
                ]);

                // Sau khi tạo hangGhe, dọn dẹp các bản ghi soft-deleted cùng (hang_ghe_id, cot)
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

                // Cập nhật suc_chua phòng
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
                'errors' => ['_global' => ['Hệ thống đã tự dọn dẹp bản ghi cũ, bạn có thể bấm "Thêm hàng" lần nữa.']],
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

    /**
     * Toggle maintenance cho toàn bộ ghế trong 1 hàng (AJAX).
     * - Client gửi 'action': 'maintenance' (bảo trì cả hàng) hoặc 'activate' (kích hoạt cả hàng)
     * - Nếu không gửi action thì tự suy ra theo trạng thái hiện tại:
     *   + Có ghế đang hoạt động → mặc định 'maintenance' (ưu tiên chuyển sang bảo trì)
     *   + Tất cả bảo trì → mặc định 'activate'
     */
    public function toggleRowMaintenance(Request $request, PhongChieu $phongChieu)
    {
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

        $soHoatDong = $ghes->where('trang_thai', 'hoat_dong')->count();
        $soBaoTri   = $ghes->where('trang_thai', 'bao_tri')->count();

        $action = $request->action;
        if (!$action) {
            $action = $soBaoTri > 0 ? 'activate' : 'maintenance';
        }

        $service = app(SeatMaintenanceService::class);

        if ($action === 'maintenance') {
            $allCanMaintain = true;
            $conflictList = [];
            $gheIdsToMaintain = [];

            foreach ($ghes as $ghe) {
                if ($ghe->trang_thai !== 'hoat_dong') {
                    continue;
                }
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
                    'message' => "Không thể bảo trì hàng {$hangGhe->ten_hang}: có {$uniqueConflicts} vé ở suất chiếu tương lai. Hãy xử lý vé trước.",
                    'conflicts_count' => $uniqueConflicts,
                ], 422);
            }

            foreach ($gheIdsToMaintain as $gheId) {
                $seat = $phongChieu->gheNgois()->findOrFail($gheId);
                $service->maintainNow($seat, auth()->id());
            }
        } else {
            foreach ($ghes as $ghe) {
                if ($ghe->trang_thai !== 'bao_tri') {
                    continue;
                }
                $pending = LichBaoTriGheNgoi::where('ghe_ngoi_id', $ghe->id)
                    ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->latest()
                    ->first();
                if ($pending) {
                    $service->completeMaintenance($pending, auth()->id());
                } else {
                    $ghe->update(['trang_thai' => 'hoat_dong']);
                }
            }
        }

        $ghes = $ghes->fresh();

        $updatedSeats = $ghes->map(function ($g) {
            return [
                'id' => $g->id,
                'trang_thai' => $g->trang_thai,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'message' => $action === 'maintenance'
                ? "Đã chuyển {$ghes->count()} ghế trong hàng {$hangGhe->ten_hang} sang bảo trì."
                : "Đã kích hoạt lại {$ghes->count()} ghế trong hàng {$hangGhe->ten_hang}.",
            'trang_thai' => $action === 'maintenance' ? 'bao_tri' : 'hoat_dong',
            'updated_seats' => $updatedSeats,
        ]);
    }

    /**
     * Delete all seats in a row (AJAX).
     */
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

        $soGhe = $ghes->count();
        $deleted = $hangGhe->gheNgois()->delete();
        $hangGhe->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa hàng {$hangGhe->ten_hang} và {$deleted} ghế.",
        ]);
    }
}
