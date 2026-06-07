<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHangGheRequest;
use App\Http\Requests\Admin\UpdateHangGheRequest;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HangGheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = HangGhe::with(['phongChieu.rapChieuPhim', 'gheNgois']);

        if ($request->has('phong_chieu_id') && $request->phong_chieu_id) {
            $query->where('phong_chieu_id', $request->phong_chieu_id);
        }

        $hangGhes = $query->orderBy('phong_chieu_id')
            ->orderBy('ten_hang')
            ->paginate(20);

        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        return view('admin.hang-ghes.index', compact('hangGhes', 'phongChieus'));
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

        return view('admin.hang-ghes.create', compact('phongChieus', 'phongChieuId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHangGheRequest $request)
    {
        $data = $request->validated();
        
        HangGhe::create($data);

        return redirect()
            ->route('admin.hang-ghes.index', ['phong_chieu_id' => $data['phong_chieu_id']])
            ->with('success', 'Hàng ghế đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HangGhe $hangGhe): View
    {
        $hangGhe->load(['phongChieu.rapChieuPhim', 'gheNgois.loaiGhe']);

        return view('admin.hang-ghes.show', compact('hangGhe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HangGhe $hangGhe): View
    {
        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        return view('admin.hang-ghes.edit', compact('hangGhe', 'phongChieus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHangGheRequest $request, HangGhe $hangGhe)
    {
        $data = $request->validated();
        
        $hangGhe->update($data);

        return redirect()
            ->route('admin.hang-ghes.index')
            ->with('success', 'Hàng ghế đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HangGhe $hangGhe)
    {
        if ($hangGhe->gheNgois()->exists()) {
            return redirect()
                ->route('admin.hang-ghes.index')
                ->with('error', 'Không thể xóa hàng ghế vì đang có ghế.');
        }

        $hangGhe->delete();

        return redirect()
            ->route('admin.hang-ghes.index')
            ->with('success', 'Hàng ghế đã được xóa thành công.');
    }

    /**
     * Update seat type for entire row.
     */
    public function updateRowType(Request $request, HangGhe $hangGhe)
    {
        $request->validate([
            'loai_ghe_id' => 'required|exists:loai_ghes,id',
        ]);

        $updated = $hangGhe->gheNgois()->update([
            'loai_ghe_id' => $request->loai_ghe_id,
        ]);

        return redirect()
            ->route('admin.hang-ghes.show', $hangGhe)
            ->with('success', "Đã cập nhật {$updated} ghế thành loại mới.");
    }
}
