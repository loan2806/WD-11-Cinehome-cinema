<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHangGheRequest;
use App\Http\Requests\Admin\UpdateHangGheRequest;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        return view('admin.hang-ghes.create', compact('phongChieus', 'phongChieuId', 'loaiGhes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHangGheRequest $request)
    {
        $data = $request->validated();
        $data['la_hang_couple'] = $request->boolean('la_hang_couple');

        $hangGhe = HangGhe::create($data);


        AdminNotificationService::push(
            '🪑 Thêm hàng ghế',
            "Đã tạo hàng {$hangGhe->ten_hang}",
            'Info'
        );


        return redirect()
            ->route('admin.hang-ghes.index', ['phong_chieu_id' => $data['phong_chieu_id']])
            ->with('success', 'Hàng ghế đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HangGhe $hangGhe): View
    {
        $hangGhe->load(['phongChieu.rapChieuPhim', 'gheNgois.loaiGhe', 'loaiGheMacDinh']);

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
        $loaiGhes = LoaiGhe::orderBy('ten_loai')->get();

        return view('admin.hang-ghes.edit', compact('hangGhe', 'phongChieus', 'loaiGhes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHangGheRequest $request, HangGhe $hangGhe)
    {
        $data = $request->validated();
        $data['la_hang_couple'] = $request->boolean('la_hang_couple');

        $hangGhe->update($data);

        AdminNotificationService::push(
            '✏️ Cập nhật hàng ghế',
            "Đã cập nhật hàng {$hangGhe->ten_hang}",
            'Info'
        );


        return redirect()
            ->route('admin.hang-ghes.index')
            ->with('success', 'Hàng ghế đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HangGhe $hangGhe)
    {
        $ghes = $hangGhe->gheNgois;
        $maGheList = $ghes->pluck('ma_ghe')->all();
        $phongChieu = $hangGhe->phongChieu;

        if (!empty($maGheList) && $phongChieu) {
            $phongCtl = app(PhongChieuController::class);
            $conflicted = $phongCtl->findSeatsInUsePublic($phongChieu, $maGheList);
            if (!empty($conflicted)) {
                $preview = implode(', ', array_slice($conflicted, 0, 10));
                $more = count($conflicted) > 10 ? '…' : '';
                return redirect()
                    ->route('admin.hang-ghes.index', ['phong_chieu_id' => $hangGhe->phong_chieu_id])
                    ->with('error', "Không thể xóa hàng {$hangGhe->ten_hang}: các ghế [{$preview}{$more}] đang có vé đã bán/đã sử dụng.");
            }
        }

        $tenHang = $hangGhe->ten_hang;

        DB::transaction(function () use ($hangGhe, $ghes) {
            // Soft delete ghế trước (sẽ tự động do cascade nếu FK + onDelete cascade, nhưng soft delete vẫn phải gọi tay)
            foreach ($ghes as $ghe) {
                $ghe->delete();
            }
            $hangGhe->delete();
        });

        AdminNotificationService::push(
            '🗑️ Xóa hàng ghế',
            "Đã xóa hàng {$tenHang}",
            'Warning'
        );

        return redirect()
            ->route('admin.hang-ghes.index', ['phong_chieu_id' => $hangGhe->phong_chieu_id])
            ->with('success', "Hàng ghế {$hangGhe->ten_hang} đã được xóa thành công.");
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

          AdminNotificationService::push(
        '🎟️ Cập nhật loại ghế theo hàng',
        "Đã đổi {$updated} ghế trong hàng {$hangGhe->ten_hang}",
        'Info'
    );

        return redirect()
            ->route('admin.hang-ghes.show', $hangGhe)
            ->with('success', "Đã cập nhật {$updated} ghế thành loại mới.");
    }

    /**
     * Trả về danh sách hàng thuộc một phòng chiếu (dùng cho AJAX / select phụ thuộc).
     */
    public function byPhongChieu(Request $request, PhongChieu $phongChieu)
    {
        $hangGhes = HangGhe::where('phong_chieu_id', $phongChieu->id)
            ->withCount('gheNgois')
            ->orderBy('ten_hang')
            ->get(['id', 'ten_hang', 'phong_chieu_id', 'la_hang_couple', 'loai_ghe_mac_dinh_id']);

        return response()->json([
            'data' => $hangGhes->map(fn($h) => [
                'id' => $h->id,
                'ten_hang' => $h->ten_hang,
                'so_ghe' => $h->ghe_ngois_count,
                'la_hang_couple' => (bool) $h->la_hang_couple,
                'loai_ghe_mac_dinh_id' => $h->loai_ghe_mac_dinh_id,
            ]),
        ]);
    }
}
