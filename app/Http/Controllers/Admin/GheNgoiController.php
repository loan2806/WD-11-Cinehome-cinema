<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGheNgoiRequest;
use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LichBaoTriGheNgoi;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Services\AdminNotificationService;
use App\Services\SeatGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GheNgoiController extends Controller
{
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
        $query = GheNgoi::with(['phongChieu.rapChieuPhim', 'hangGhe', 'loaiGhe']);

        if ($request->has('phong_chieu_id') && $request->phong_chieu_id) {
            $query->where('phong_chieu_id', $request->phong_chieu_id);
        }

        if ($request->has('loai_ghe_id') && $request->loai_ghe_id) {
            $query->where('loai_ghe_id', $request->loai_ghe_id);
        }

        if ($request->has('trang_thai') && $request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $gheNgois = $query->orderBy('phong_chieu_id')
            ->orderBy('ma_ghe')
            ->paginate(30);

        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        return view('admin.ghe-ngois.index', compact(
            'gheNgois',
            'phongChieus',
            'loaiGhes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $phongChieuId = $request->phong_chieu_id;
        $hangGheId = $request->hang_ghe_id;

        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->where('trang_thai', 'hoat_dong')
            ->orderBy('ten_phong')
            ->get();

        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        // Nếu đã chọn phòng thì load sẵn các hàng thuộc phòng đó
        $hangGhes = $phongChieuId
            ? HangGhe::where('phong_chieu_id', $phongChieuId)
            ->orderBy('ten_hang')
            ->get()
            : collect();

        // Gợi ý cột kế tiếp cho hàng đã chọn (nếu có)
        $goiYCot = 1;
        $goiYMaGhe = '';
        if ($hangGheId) {
            $hangGhe = HangGhe::find($hangGheId);
            if ($hangGhe) {
                $cotLonNhat = (int) GheNgoi::where('hang_ghe_id', $hangGhe->id)->max('cot');
                $goiYCot = $cotLonNhat + 1;
                $goiYMaGhe = $hangGhe->ten_hang . $goiYCot;
            }
        }

        return view('admin.ghe-ngois.create', compact(
            'phongChieus',
            'phongChieuId',
            'hangGhes',
            'hangGheId',
            'loaiGhes',
            'goiYCot',
            'goiYMaGhe'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGheNgoiRequest $request)
    {
        $data = $request->validated();

        $ghe = GheNgoi::create($data);

        // Nếu ghế vừa tạo thuộc loại couple, tự gán couple_group_id ghép với ghế liền kề
        $this->seatGenerator->attachCoupleGroupForSeat($ghe);

        // Nếu người dùng tick "tiếp tục thêm ghế cho hàng này" thì quay lại form
        if ($request->boolean('tiep_tuc_tao')) {
            return redirect()
                ->route('admin.ghe-ngois.create', [
                    'phong_chieu_id' => $data['phong_chieu_id'],
                    'hang_ghe_id' => $data['hang_ghe_id'],
                ])
                ->with('success', "Đã tạo ghế {$ghe->ma_ghe}. Hãy tạo ghế tiếp theo.");
        }

        AdminNotificationService::push(
            '🪑 Thêm ghế',
            "Đã thêm ghế {$ghe->ma_ghe}",
            'Success'
        );

        return redirect()
            ->route('admin.ghe-ngois.index', ['phong_chieu_id' => $data['phong_chieu_id']])
            ->with('success', "Ghế {$ghe->ma_ghe} đã được tạo thành công.");
    }

    /**
     * Display the specified resource.
     */
    public function show(GheNgoi $gheNgoi): View
    {
        $gheNgoi->load(['phongChieu.rapChieuPhim', 'hangGhe', 'loaiGhe']);

        return view('admin.ghe-ngois.show', compact('gheNgoi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GheNgoi $gheNgoi): View
    {
        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        return view('admin.ghe-ngois.edit', compact(
            'gheNgoi',
            'phongChieus',
            'loaiGhes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GheNgoi $gheNgoi)
    {
        $request->validate([
            'loai_ghe_id' => 'required|exists:loai_ghes,id',
            'trang_thai' => 'required|in:hoat_dong,bao_tri',
        ]);

        $gheNgoi->update($request->only(['loai_ghe_id', 'trang_thai']));

        AdminNotificationService::push(
            '✏️ Cập nhật ghế',
            "Đã cập nhật ghế {$gheNgoi->ma_ghe}",
            'Warning'
        );

        return redirect()
            ->route('admin.ghe-ngois.index')
            ->with('success', 'Ghế ngồi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GheNgoi $gheNgoi)
    {
        $phongChieuId = $gheNgoi->phong_chieu_id;
        $maGhe = $gheNgoi->ma_ghe;
        $coupleGroupId = $gheNgoi->couple_group_id;
        $phongChieu = $gheNgoi->phongChieu;

        // Kiểm tra ghế có vé đang sử dụng không (tránh xóa nhầm)
        if ($phongChieu) {
            $conflicted = app(PhongChieuController::class)
                ->findSeatsInUsePublic($phongChieu, [$maGhe]);
            if (!empty($conflicted)) {
                $message = "Không thể xóa ghế {$maGhe}: đang có vé đã bán/đã sử dụng.";
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()
                    ->route('admin.ghe-ngois.index', ['phong_chieu_id' => $phongChieuId])
                    ->with('error', $message);
            }
        }

        DB::transaction(function () use ($gheNgoi, $coupleGroupId, $maGhe) {
            // Soft delete ghế
            $gheNgoi->delete();

            // Nếu ghế thuộc cặp couple, dọn couple_group_id của ghế còn lại
            // để tránh "mồ côi" trỏ vào group_id không còn tồn tại
            if ($coupleGroupId) {
                GheNgoi::where('couple_group_id', $coupleGroupId)
                    ->where('id', '!=', $gheNgoi->id)
                    ->update(['couple_group_id' => null]);
            }
        });

        $message = "Ghế {$maGhe} đã được xóa.";

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        AdminNotificationService::push(
            '🗑️ Xóa ghế',
            "Đã xóa ghế {$maGhe}",
            'Danger'
        );

        return redirect()
            ->route('admin.ghe-ngois.index', ['phong_chieu_id' => $phongChieuId])
            ->with('success', $message);
    }

    /**
     * Check maintenance conflicts (AJAX).
     */
    public function checkConflicts(Request $request, GheNgoi $gheNgoi)
    {
        $service = app(SeatMaintenanceService::class);
        $result = $service->canMaintainNow($gheNgoi);
        $coupleSiblings = $service->getCoupleSiblings($gheNgoi);

        return response()->json([
            'success' => true,
            'can_maintain' => $result['can'],
            'conflicts' => $result['conflicts'],
            'couple_siblings' => $coupleSiblings,
            'is_maintenance' => $gheNgoi->trang_thai === 'bao_tri',
            'is_couple' => !empty($gheNgoi->couple_group_id),
        ]);
    }

    /**
     * Toggle maintenance status for a seat (AJAX) with conflict check.
     */
    public function toggleMaintenance(Request $request, GheNgoi $gheNgoi)
    {
        $service = app(SeatMaintenanceService::class);

        if ($gheNgoi->trang_thai !== 'bao_tri') {
            $result = $service->canMaintainNow($gheNgoi);
            if (!$result['can']) {
                return response()->json([
                    'success' => false,
                    'message' => $this->buildConflictMessage($result['conflicts']),
                    'conflicts' => $result['conflicts'],
                ], 422);
            }
        }

        $isMaintenance = $gheNgoi->trang_thai !== 'bao_tri';
        $service->maintainNow($gheNgoi, auth()->id());

        $message = $isMaintenance
            ? 'Ghế đã được đưa vào bảo trì.'
            : 'Ghế đã được kích hoạt trở lại.';

        $this->ghiNhatKy($request, $isMaintenance ? 'Bảo trì ghế' : 'Kích hoạt lại ghế', 'Quản lý phòng & ghế', "{$message} {$gheNgoi->ma_ghe}");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'trang_thai' => $gheNgoi->fresh()->trang_thai,
                'is_maintenance' => $isMaintenance,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Schedule maintenance for a seat (AJAX).
     */
    public function scheduleMaintenance(Request $request, GheNgoi $gheNgoi)
    {
        $request->validate([
            'thoi_gian_bat_dau' => ['required', 'date', 'after:now'],
            'thoi_gian_ket_thuc' => ['nullable', 'date', 'after:thoi_gian_bat_dau'],
            'ly_do' => ['nullable', 'string', 'max:500'],
        ]);

        $service = app(SeatMaintenanceService::class);

        try {
            $lich = $service->scheduleMaintenance(
                $gheNgoi,
                $request->date('thoi_gian_bat_dau'),
                $request->filled('thoi_gian_ket_thuc') ? $request->date('thoi_gian_ket_thuc') : null,
                auth()->id(),
                $request->input('ly_do')
            );

            $this->ghiNhatKy($request, 'Lên lịch bảo trì ghế', 'Quản lý phòng & ghế', "Lên lịch bảo trì ghế {$gheNgoi->ma_ghe} lúc {$lich->thoi_gian_bat_dau}");

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã lên lịch bảo trì ghế thành công.',
                    'lich_id' => $lich->id,
                    'thoi_gian_bat_dau' => $lich->thoi_gian_bat_dau,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Đã lên lịch bảo trì ghế thành công.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Complete maintenance for a seat (AJAX).
     */
    public function completeMaintenance(Request $request, LichBaoTriGheNgoi $lichBaoTriGheNgoi)
    {
        $request->validate([
            'ghi_chu' => ['nullable', 'string', 'max:500'],
        ]);

        $service = app(SeatMaintenanceService::class);
        $ghe = $service->completeMaintenance($lichBaoTriGheNgoi, auth()->id(), $request->input('ghi_chu'));

        $this->ghiNhatKy($request, 'Kết thúc bảo trì ghế', 'Quản lý phòng & ghế', "Kết thúc bảo trì ghế {$ghe->ma_ghe}");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã kích hoạt lại ghế thành công.',
                'trang_thai' => $ghe->trang_thai,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Đã kích hoạt lại ghế thành công.');
    }

    protected function buildConflictMessage(array $conflicts): string
    {
        if (empty($conflicts)) {
            return 'Không thể bảo trì ghế lúc này.';
        }

        $count = count($conflicts);
        $preview = implode(', ', array_slice(array_column($conflicts, 'ma_ve'), 0, 10));
        $more = $count > 10 ? '...' : '';

        return "Không thể bảo trì: có {$count} vé bị ảnh hưởng [{$preview}{$more}]. Vui lòng xử lý vé trước.";
    }

    public function baoTri(Request $request): View
    {
        $query = LichBaoTriGheNgoi::with(['gheNgoi.hangGhe', 'gheNgoi.phongChieu.rapChieuPhim', 'nguoiDung']);

        if ($request->has('ghe_ngoi_id') && $request->ghe_ngoi_id) {
            $query->where('ghe_ngoi_id', $request->ghe_ngoi_id);
        }

        if ($request->has('trang_thai') && $request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }

        if ($request->has('tu_ngay') && $request->tu_ngay) {
            $query->whereDate('thoi_gian_bat_dau', '>=', $request->tu_ngay);
        }

        if ($request->has('den_ngay') && $request->den_ngay) {
            $query->whereDate('thoi_gian_bat_dau', '<=', $request->den_ngay);
        }

        $lichBaoTriGheNgois = $query->orderByDesc('thoi_gian_bat_dau')->paginate(20);

        return view('admin.ghe-ngois.bao-tri', compact('lichBaoTriGheNgois'));
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
            // Không chặn luồng chính nếu ghi log lỗi
        }
    }
}
