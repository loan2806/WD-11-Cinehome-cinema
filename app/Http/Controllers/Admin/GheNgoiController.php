<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGheNgoiRequest;
use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Services\SeatGeneratorService;
use Illuminate\Http\Request;
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
        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->where('trang_thai', 'hoat_dong')
            ->orderBy('ten_phong')
            ->get();

        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        return view('admin.ghe-ngois.create', compact(
            'phongChieus',
            'phongChieuId',
            'loaiGhes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGheNgoiRequest $request)
    {
        $data = $request->validated();
        
        $exists = GheNgoi::where('phong_chieu_id', $data['phong_chieu_id'])
            ->where('ma_ghe', $data['ma_ghe'])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Mã ghế đã tồn tại trong phòng chiếu này.');
        }

        GheNgoi::create($data);

        return redirect()
            ->route('admin.ghe-ngois.index', ['phong_chieu_id' => $data['phong_chieu_id']])
            ->with('success', 'Ghế ngồi đã được tạo thành công.');
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

        return redirect()
            ->route('admin.ghe-ngois.index')
            ->with('success', 'Ghế ngồi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GheNgoi $gheNgoi)
    {
        $gheNgoi->delete();

        $message = 'Ghế đã được xóa.';

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('admin.ghe-ngois.index')
            ->with('success', $message);
    }

    /**
     * Toggle maintenance status for a seat.
     */
    public function toggleMaintenance(GheNgoi $gheNgoi)
    {
        $isMaintenance = $gheNgoi->trang_thai !== 'bao_tri';

        $this->seatGenerator->toggleMaintenance($gheNgoi, $isMaintenance);

        $message = $isMaintenance
            ? 'Ghế đã được đưa vào bảo trì.'
            : 'Ghế đã được kích hoạt trở lại.';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'trang_thai' => $gheNgoi->fresh()->trang_thai,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Bulk toggle maintenance status.
     */
    public function bulkMaintenance(Request $request)
    {
        $request->validate([
            'ghe_ids' => 'required|array',
            'ghe_ids.*' => 'exists:ghe_ngois,id',
            'trang_thai' => 'required|in:hoat_dong,bao_tri',
        ]);

        $updated = GheNgoi::whereIn('id', $request->ghe_ids)
            ->update(['trang_thai' => $request->trang_thai]);

        $message = "Đã cập nhật {$updated} ghế.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }
}
