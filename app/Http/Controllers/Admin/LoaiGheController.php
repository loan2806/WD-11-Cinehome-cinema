<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLoaiGheRequest;
use App\Http\Requests\Admin\UpdateLoaiGheRequest;
use App\Models\LoaiGhe;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoaiGheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $loaiGhes = LoaiGhe::withCount('gheNgois')
            ->orderBy('id')
            ->get();

        return view('admin.loai-ghes.index', compact('loaiGhes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.loai-ghes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoaiGheRequest $request)
    {
        $data = $request->validated();
        $data['la_couple'] = $request->boolean('la_couple');

        LoaiGhe::create($data);

        return redirect()
            ->route('admin.loai-ghes.index')
            ->with('success', 'Loại ghế đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoaiGhe $loaiGhe): View
    {
        $loaiGhe->load(['gheNgois' => function ($query) {
            $query->with('phongChieu.rapChieuPhim')
                ->orderBy('ma_ghe');
        }]);

        return view('admin.loai-ghes.show', compact('loaiGhe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoaiGhe $loaiGhe): View
    {
        return view('admin.loai-ghes.edit', compact('loaiGhe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLoaiGheRequest $request, LoaiGhe $loaiGhe)
    {
        $data = $request->validated();
        $data['la_couple'] = $request->boolean('la_couple');

        $loaiGhe->update($data);

        return redirect()
            ->route('admin.loai-ghes.index')
            ->with('success', 'Loại ghế đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoaiGhe $loaiGhe)
    {
        if ($loaiGhe->gheNgois()->exists()) {
            return redirect()
                ->route('admin.loai-ghes.index')
                ->with('error', 'Không thể xóa loại ghế vì đang có ghế sử dụng.');
        }

        $loaiGhe->delete();

        return redirect()
            ->route('admin.loai-ghes.index')
            ->with('success', 'Loại ghế đã được xóa thành công.');
    }
}
