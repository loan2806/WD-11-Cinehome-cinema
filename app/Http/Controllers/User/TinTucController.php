<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DanhMucTin;
use App\Models\TinTuc;
use App\Models\Voucher;
use App\Models\NguoiDungVoucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TinTucController extends Controller
{
    public function index(Request $request): View
    {
        $danhMucs = DanhMucTin::active()->orderByThuTu()->get();

        $query = TinTuc::with(['danhMucTin', 'tags'])
            ->active()
            ->orderByNgayDang();

        // Filter theo danh mục
        if ($request->has('danh_muc') && $request->danh_muc) {
            $danhMuc = DanhMucTin::where('slug', $request->danh_muc)->first();
            if ($danhMuc) {
                $query->where('danh_muc_tin_id', $danhMuc->id);
            }
        }

        // Filter theo tag
        if ($request->has('tag') && $request->tag) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tieu_de', 'like', '%' . $search . '%')
                    ->orWhere('mo_ta_ngan', 'like', '%' . $search . '%');
            });
        }

        // Tin nổi bật
        $tinNoiBat = $query->clone()->noiBat()->limit(3)->get();

        // Tin mới nhất với phân trang
        $tinTucs = $query->paginate(9)->withQueryString();

        // Vouchers đang hoạt động
        $vouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', now())
            ->orderBy('ngay_het_han', 'asc')
            ->limit(6)
            ->get();

        return view('user.tin-tuc.index', compact(
            'danhMucs',
            'tinTucs',
            'tinNoiBat',
            'vouchers'
        ));
    }

    public function show(string $slug): View
    {
        $tinTuc = TinTuc::with(['danhMucTin', 'tags'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Tăng lượt xem
        $tinTuc->incrementLuotXem();

        // Tin liên quan cùng danh mục
        $tinLienQuan = TinTuc::with(['danhMucTin'])
            ->active()
            ->where('danh_muc_tin_id', $tinTuc->danh_muc_tin_id)
            ->where('id', '!=', $tinTuc->id)
            ->orderByNgayDang()
            ->limit(4)
            ->get();

        // Vouchers đang hoạt động
        $vouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', now())
            ->orderBy('ngay_het_han', 'asc')
            ->limit(4)
            ->get();

        // Kiểm tra voucher đã lưu tạm
        $voucherTam = session('voucher_tam');

        return view('user.tin-tuc.show', compact(
            'tinTuc',
            'tinLienQuan',
            'vouchers',
            'voucherTam'
        ));
    }

    public function voucherIndex(): View
    {
        $vouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', now())
            ->orderBy('ngay_het_han', 'asc')
            ->paginate(12);

        return view('user.voucher.index-tin-tuc', compact('vouchers'));
    }
}
