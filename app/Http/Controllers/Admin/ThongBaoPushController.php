<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreThongBaoPushRequest;
use App\Models\NguoiDung;
use App\Models\ThongBaoPush;
use App\Models\ThongBaoPushNguoiDung;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ThongBaoPushController extends Controller
{
    use Loggable;

    /**
     * Danh sách thông báo đẩy.
     */
    public function index(Request $request): View
    {
        $query = ThongBaoPush::with('nguoiTao');

        // Tìm kiếm theo tiêu đề
        if ($request->has('search') && $request->search) {
            $query->where('tieu_de', 'like', '%' . $request->search . '%');
        }

        // Lọc theo loại thông báo
        if ($request->has('loai') && $request->loai) {
            $query->where('loai', $request->loai);
        }

        // Lọc theo trạng thái
        if ($request->has('trang_thai') && $request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $thongBaos = $query->latest()->paginate(15)->withQueryString();

        return view('admin.thong-bao-push.index', compact('thongBaos'));
    }

    /**
     * Form tạo mới thông báo đẩy.
     */
    public function create(): View
    {
        $loaiOptions = [
            'info' => 'Thông tin (Info)',
            'success' => 'Thành công (Success)',
            'warning' => 'Cảnh báo (Warning)',
            'error' => 'Lỗi (Error)',
        ];

        $doiTuongOptions = [
            'all' => 'Tất cả người dùng',
            'khach_hang' => 'Khách hàng',
            'nhan_vien' => 'Nhân viên',
            'quan_tri_vien' => 'Quản trị viên',
            'nguoi_dung_cu_the' => 'Người dùng cụ thể',
        ];

        return view('admin.thong-bao-push.create', compact(
            'loaiOptions',
            'doiTuongOptions'
        ));
    }

    /**
     * Lưu thông báo đẩy mới.
     */
    public function store(StoreThongBaoPushRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Tạo thông báo đẩy
            $thongBao = ThongBaoPush::create([
                'tieu_de' => $validated['tieu_de'],
                'noi_dung' => $validated['noi_dung'],
                'loai' => $validated['loai'],
                'doi_tuong_nhan' => $validated['doi_tuong_nhan'],
                'nguoi_tao_id' => auth()->id(),
                'trang_thai' => 'da_gui',
                'thoi_gian_gui' => now(),
            ]);

            // Gửi thông báo đến người nhận
            $this->guiThongBao($thongBao, $validated['doi_tuong_nhan'], $validated['nguoi_dung_cu_the'] ?? null);

            DB::commit();

            // Ghi nhật ký hoạt động
            $this->ghiNhatKy(
                $request,
                'Tạo thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Tạo thông báo: {$validated['tieu_de']}",
                [
                    'loai' => $validated['loai'],
                    'doi_tuong_nhan' => $validated['doi_tuong_nhan'],
                ]
            );

            // Gửi thông báo admin
            AdminNotificationService::push(
                '📢 Thông báo đẩy',
                "Đã tạo thông báo: {$validated['tieu_de']}",
                'Info'
            );

            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with('success', 'Thông báo đẩy đã được tạo và gửi thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Đã xảy ra lỗi khi tạo thông báo đẩy: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Xem chi tiết thông báo đẩy.
     */
    public function show(ThongBaoPush $thongBaoPush): View
    {
        $thongBaoPush->load('nguoiTao');

        // Lấy danh sách người nhận cụ thể nếu có
        $nguoiNhanList = [];
        if ($thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the') {
            $nguoiNhanList = ThongBaoPushNguoiDung::with('nguoiDung')
                ->where('thong_bao_push_id', $thongBaoPush->id)
                ->get()
                ->pluck('nguoiDung');
        }

        return view('admin.thong-bao-push.show', compact(
            'thongBaoPush',
            'nguoiNhanList'
        ));
    }

    /**
     * Xóa thông báo đẩy.
     */
    public function destroy(Request $request, ThongBaoPush $thongBaoPush): RedirectResponse
    {
        // Chỉ cho phép Quản trị viên hoặc Quản lý hệ thống xóa
        $user = auth()->user();
        if (!$user->hasRole('Quản trị viên') && !$user->hasRole('Quản lý hệ thống')) {
            return redirect()
                ->back()
                ->with('error', 'Bạn không có quyền xóa thông báo đẩy.');
        }

        $tieuDe = $thongBaoPush->tieu_de;

        // Xóa các bản ghi trung gian trước
        ThongBaoPushNguoiDung::where('thong_bao_push_id', $thongBaoPush->id)->delete();

        // Xóa thông báo đẩy
        $thongBaoPush->delete();

        // Ghi nhật ký hoạt động
        $this->ghiNhatKy(
            $request,
            'Xóa thông báo đẩy',
            'Quản lý thông báo đẩy',
            "Xóa thông báo: {$tieuDe}",
            [
                'id' => $thongBaoPush->id,
                'tieu_de' => $tieuDe,
            ]
        );

        // Gửi thông báo admin
        AdminNotificationService::push(
            '🗑️ Xóa thông báo đẩy',
            "Đã xóa thông báo: {$tieuDe}",
            'Warning'
        );

        return redirect()
            ->route('admin.thong-bao-push.index')
            ->with('success', 'Thông báo đẩy đã được xóa thành công.');
    }

    /**
     * Lấy danh sách người dùng theo vai trò (AJAX).
     */
    public function getUsersByRole(Request $request): JsonResponse
    {
        $role = $request->get('role');

        $query = NguoiDung::query();

        switch ($role) {
            case 'khach_hang':
                $query->where('vai_tro', 'user');
                break;
            case 'nhan_vien':
                $query->where('vai_tro', 'nhan_vien');
                break;
            case 'quan_tri_vien':
                $query->where(function ($q) {
                    $q->where('vai_tro', 'admin')
                        ->orWhere('vai_tro', 'quan_ly_he_thong');
                });
                break;
            default:
                $query->where('id', '<=', 0); // Trả về rỗng
                break;
        }

        $users = $query->select('id', 'ho_ten', 'email')->get();

        return response()->json($users);
    }

    /**
     * Gửi thông báo đến người nhận.
     */
    private function guiThongBao(ThongBaoPush $thongBao, string $doiTuongNhan, ?int $nguoiDungCuThe = null): void
    {
        switch ($doiTuongNhan) {
            case 'all':
                // Gửi đến tất cả người dùng
                $this->guiDenTatCaNguoiDung($thongBao);
                break;

            case 'khach_hang':
                // Gửi đến khách hàng
                $this->guiDenKhachHang($thongBao);
                break;

            case 'nhan_vien':
                // Gửi đến nhân viên
                $this->guiDenNhanVien($thongBao);
                break;

            case 'quan_tri_vien':
                // Gửi đến quản trị viên
                $this->guiDenQuanTriVien($thongBao);
                break;

            case 'nguoi_dung_cu_the':
                // Gửi đến người dùng cụ thể
                if ($nguoiDungCuThe) {
                    ThongBaoPushNguoiDung::create([
                        'thong_bao_push_id' => $thongBao->id,
                        'nguoi_dung_id' => $nguoiDungCuThe,
                    ]);
                }
                break;
        }
    }

    /**
     * Gửi thông báo đến tất cả người dùng.
     */
    private function guiDenTatCaNguoiDung(ThongBaoPush $thongBao): void
    {
        $nguoiDungs = NguoiDung::select('id')->get();

        foreach ($nguoiDungs as $nguoiDung) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $nguoiDung->id,
            ]);
        }
    }

    /**
     * Gửi thông báo đến khách hàng.
     */
    private function guiDenKhachHang(ThongBaoPush $thongBao): void
    {
        $khachHangs = NguoiDung::where('vai_tro', 'user')->select('id')->get();

        foreach ($khachHangs as $khachHang) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $khachHang->id,
            ]);
        }
    }

    /**
     * Gửi thông báo đến nhân viên.
     */
    private function guiDenNhanVien(ThongBaoPush $thongBao): void
    {
        $nhanViens = NguoiDung::where('vai_tro', 'nhan_vien')->select('id')->get();

        foreach ($nhanViens as $nhanVien) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $nhanVien->id,
            ]);
        }
    }

    /**
     * Gửi thông báo đến quản trị viên.
     */
    private function guiDenQuanTriVien(ThongBaoPush $thongBao): void
    {
        $quanTriViens = NguoiDung::where(function ($q) {
            $q->where('vai_tro', 'admin')
                ->orWhere('vai_tro', 'quan_ly_he_thong');
        })->select('id')->get();

        foreach ($quanTriViens as $quanTriVien) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $quanTriVien->id,
            ]);
        }
    }
}
