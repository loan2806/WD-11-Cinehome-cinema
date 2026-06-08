<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhongChieuRequest;
use App\Http\Requests\Admin\UpdatePhongChieuRequest;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Services\SeatGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhongChieuController extends Controller
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
        $rapChieuPhims = RapChieuPhim::where('trang_thai', 'hoat_dong')->orderBy('ten_rap')->get();

        return view('admin.phong-chieus.create', compact('rapChieuPhims'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhongChieuRequest $request)
    {
        $data = $request->validated();
        
        PhongChieu::create($data);

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

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhongChieu $phongChieu)
    {
        if ($phongChieu->suatChieus()->exists()) {
            return redirect()
                ->route('admin.phong-chieus.index')
                ->with('error', 'Không thể xóa phòng chiếu vì đang có suất chiếu.');
        }

        $phongChieu->delete();

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa thành công.');
    }

    /**
     * Force delete the specified resource.
     */
    public function forceDestroy($id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->forceDelete();

        return redirect()
            ->route('admin.phong-chieus.index')
            ->with('success', 'Phòng chiếu đã được xóa vĩnh viễn.');
    }

    /**
     * Restore the specified resource.
     */
    public function restore($id)
    {
        $phongChieu = PhongChieu::withTrashed()->findOrFail($id);
        $phongChieu->restore();

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
}
