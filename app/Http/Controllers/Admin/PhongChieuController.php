<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhongChieuRequest;
use App\Http\Requests\Admin\UpdatePhongChieuRequest;
use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\VeXemPhim;
use App\Services\AdminNotificationService;
use App\Services\SeatGeneratorService;
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

    public function index(Request $request): View
    {
        $query = PhongChieu::with(['rapChieuPhim', 'hangGhes', 'gheNgois']);

        if ($request->rap_chieu_phim_id) {
            $query->where('rap_chieu_phim_id', $request->rap_chieu_phim_id);
        }

        if ($request->trang_thai) {
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

        $phongChieu = PhongChieu::create($data);

        AdminNotificationService::push(
            '➕ Tạo phòng chiếu',
            "Đã tạo phòng {$phongChieu->ten_phong}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm phòng chiếu',
            'Quản lý phòng & ghế',
            "Thêm phòng: {$phongChieu->ten_phong}"
        );

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được tạo thành công.');
    }

    public function show(PhongChieu $phongChieu): View
    {
        $phongChieu->load(['rapChieuPhim', 'hangGhes.gheNgois.loaiGhe', 'suatChieus.phim']);

        $seatMap = $this->seatGenerator->getSeatMap($phongChieu);

        return view('admin.phong-chieus.show', [
            'phongChieu' => $phongChieu,
            'seatMap' => $seatMap,
            'soHang' => $phongChieu->hangGhes->count(),
            'soCot' => $phongChieu->gheNgois->max('cot') ?? 0,
        ]);
    }

    public function edit(PhongChieu $phongChieu): View
    {
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        return view('admin.phong-chieus.edit', compact('phongChieu', 'rapChieuPhims'));
    }

    public function update(UpdatePhongChieuRequest $request, PhongChieu $phongChieu)
    {
        $data = $request->validated();
        $phongChieu->update($data);

        $tenPhong = $phongChieu->ten_phong;

        AdminNotificationService::push(
            '✏️ Cập nhật phòng chiếu',
            "Đã cập nhật phòng {$tenPhong}",
            'Info'
        );

        $this->ghiNhatKy(
            $request,
            'Cập nhật phòng chiếu',
            'Quản lý phòng & ghế',
            "Cập nhật phòng: {$tenPhong}"
        );

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

        $this->ghiNhatKy(
            $request,
            'Xóa phòng chiếu',
            'Quản lý phòng & ghế',
            "Xóa phòng: {$tenPhong}"
        );

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa thành công.');
    }

    public function restore(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->restore();

        $tenPhong = $phongChieu->ten_phong;

        AdminNotificationService::push(
            '♻️ Khôi phục phòng chiếu',
            "Đã khôi phục phòng {$tenPhong}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Khôi phục phòng chiếu',
            'Quản lý phòng & ghế',
            "Khôi phục phòng: {$tenPhong}"
        );

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được khôi phục.');
    }

    public function forceDestroy(Request $request, $id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->forceDelete();

        $this->ghiNhatKy(
            $request,
            'Xóa vĩnh viễn phòng chiếu',
            'Quản lý phòng & ghế',
            "Xóa vĩnh viễn phòng #{$id}"
        );

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn phòng chiếu.');
    }

    // ========================
    // SEAT FUNCTIONS
    // ========================

    public function generateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'so_hang' => 'required|integer|min:1|max:20',
            'so_cot' => 'required|integer|min:1|max:20',
        ]);

        try {
            $result = $this->seatGenerator->generateSeats(
                $phongChieu,
                $request->so_hang,
                $request->so_cot,
                $request->loai_ghe_thuong_id,
                $request->loai_ghe_vip_id,
                $request->loai_ghe_couple_id,
                true
            );

            return back()->with('success', "Đã tạo {$result['tong_so_ghe']} ghế.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ========================
    // BULK UPDATE
    // ========================

    public function bulkUpdateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'ghe_ids' => 'required|array',
            'action' => 'required',
        ]);

        $gheIds = $request->ghe_ids;
        $action = $request->action;

        $seats = GheNgoi::whereIn('id', $gheIds)->get();
        $updated = [];

        if ($action === 'maintenance') {
            GheNgoi::whereIn('id', $gheIds)->update(['trang_thai' => 'bao_tri']);

            foreach ($seats as $s) {
                $updated[] = ['id' => $s->id, 'trang_thai' => 'bao_tri'];
            }
        }

        if ($action === 'activate') {
            GheNgoi::whereIn('id', $gheIds)->update(['trang_thai' => 'hoat_dong']);

            foreach ($seats as $s) {
                $updated[] = ['id' => $s->id, 'trang_thai' => 'hoat_dong'];
            }
        }

        return response()->json([
            'success' => true,
            'updated_seats' => $updated
        ]);
    }

    // ========================
    // CHECK SEATS IN USE
    // ========================

    protected function findSeatsInUse(PhongChieu $phongChieu, array $maGheCanKiemTra): array
    {
        if (!$maGheCanKiemTra) return [];

        $maGheTrongVe = VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where('ten_phong', $phongChieu->ten_phong)
            ->pluck('ma_ghe')
            ->toArray();

        $conflict = [];

        foreach ($maGheCanKiemTra as $ma) {
            foreach ($maGheTrongVe as $csv) {
                if (str_contains($csv, $ma)) {
                    $conflict[] = $ma;
                }
            }
        }

        return array_unique($conflict);
    }
}