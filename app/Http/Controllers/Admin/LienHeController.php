<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LienHePhanHoiMail;
use App\Models\LienHe;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LienHeController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = LienHe::with('nguoiDung')->latest();

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        if ($request->filled('tim_kiem')) {
            $tuKhoa = $request->string('tim_kiem');
            $query->where(function ($q) use ($tuKhoa) {
                $q->where('ho_ten', 'like', "%{$tuKhoa}%")
                    ->orWhere('email', 'like', "%{$tuKhoa}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$tuKhoa}%");
            });
        }

        $lienHes = $query->paginate(10)->withQueryString();

        $thongKe = [
            'tong' => LienHe::count(),
            'cho_xu_ly' => LienHe::where('trang_thai', 'cho_xu_ly')->count(),
            'dang_xu_ly' => LienHe::where('trang_thai', 'dang_xu_ly')->count(),
            'da_xu_ly' => LienHe::where('trang_thai', 'da_xu_ly')->count(),
        ];

        $soChoXuLy = $thongKe['cho_xu_ly'];

        return view('admin.lien-he.index', compact('lienHes', 'soChoXuLy', 'thongKe'));
    }

    public function show(LienHe $lienHe)
    {
        $lienHe->load(['nguoiDung', 'nguoiXuLy']);

        return view('admin.lien-he.show', compact('lienHe'));
    }

    public function update(Request $request, LienHe $lienHe)
    {
        $data = $request->validate([
            'trang_thai' => ['required', 'in:cho_xu_ly,dang_xu_ly,da_xu_ly'],
            'phan_hoi' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['nguoi_xu_ly_id'] = auth()->id();
        if ($data['trang_thai'] === 'da_xu_ly') {
            $data['thoi_gian_xu_ly'] = now();
        }

        $phanHoiCu = $lienHe->phan_hoi;

        $lienHe->update($data);

        $daGuiEmail = false;
        if (!empty($data['phan_hoi']) && $data['phan_hoi'] !== $phanHoiCu) {
            Mail::to($lienHe->email)->send(new LienHePhanHoiMail($lienHe));
            $daGuiEmail = true;
        }

        $this->ghiNhatKy($request, 'Cập nhật liên hệ khách hàng', 'Quản lý liên hệ', "Cập nhật liên hệ #{$lienHe->id} sang {$data['trang_thai']}");

        return back()->with('success', $daGuiEmail
            ? 'Đã cập nhật liên hệ và gửi email phản hồi tới khách hàng.'
            : 'Đã cập nhật liên hệ.');
    }

    public function destroy(Request $request, LienHe $lienHe)
    {
        $id = $lienHe->id;
        $lienHe->delete();

        $this->ghiNhatKy($request, 'Xóa liên hệ khách hàng', 'Quản lý liên hệ', "Xóa liên hệ #{$id}");

        return redirect()->route('admin.lien-he.index')->with('success', 'Đã xóa liên hệ.');
    }
}
