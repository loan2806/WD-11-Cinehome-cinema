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
        $summary = [
            'total' => ThongBaoPush::count(),
            'sent' => ThongBaoPush::where('trang_thai', 'da_gui')->count(),
            'promo' => ThongBaoPush::where('loai', 'promo')->count(),
            'today' => ThongBaoPush::whereDate('created_at', now()->toDateString())->count(),
        ];

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

        // Lọc theo đối tượng nhận
        if ($request->has('doi_tuong_nhan') && $request->doi_tuong_nhan) {
            $query->where('doi_tuong_nhan', $request->doi_tuong_nhan);
        }

        $thongBaos = $query->latest()->paginate(15)->withQueryString();

        return view('admin.thong-bao-push.index', compact('thongBaos', 'summary'));
    }

    /**
     * Form tạo mới thông báo đẩy.
     */
    public function create(): View
    {
        $loaiOptions = [
            'info' => 'Thông tin',
            'success' => 'Thành công',
            'warning' => 'Cảnh báo',
            'promo' => 'Khuyến mãi',
            'system' => 'Hệ thống',
        ];

        $doiTuongOptions = [
            'all' => 'Tất cả người dùng',
            'user' => 'Khách hàng thường',
            'vip' => 'Khách hàng VIP',
            'staff' => 'Nhân viên',
            'admin' => 'Quản trị viên',
            'nguoi_dung_cu_the' => 'Người dùng cụ thể',
        ];

        $audienceCounts = [
            'all' => NguoiDung::count(),
            'user' => NguoiDung::where('vai_tro', 'user')->count(),
            'vip' => NguoiDung::where('vai_tro', 'vip')->count(),
            'staff' => NguoiDung::where('vai_tro', 'nhan_vien')->count(),
            'admin' => NguoiDung::where(function ($query) {
                $query->where('vai_tro', 'admin')
                    ->orWhere('vai_tro', 'quan_ly_he_thong');
            })->count(),
        ];

        return view('admin.thong-bao-push.create', compact(
            'loaiOptions',
            'doiTuongOptions',
            'audienceCounts'
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
                '📢 Thông báo đẩy: ' . $validated['tieu_de'],
                $validated['noi_dung'],
                $validated['loai']
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
        $recipientCount = ThongBaoPushNguoiDung::where('thong_bao_push_id', $thongBaoPush->id)->count();

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
            'nguoiNhanList',
            'recipientCount'
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
            case 'user':
                $query->where('vai_tro', 'user');
                break;
            case 'staff':
                $query->where('vai_tro', 'nhan_vien');
                break;
            case 'admin':
                $query->where(function ($q) {
                    $q->where('vai_tro', 'admin')
                        ->orWhere('vai_tro', 'quan_ly_he_thong');
                });
                break;
            case 'vip':
                $query->where('vai_tro', 'vip');
                break;
            case 'nguoi_dung_cu_the':
                // Lấy tất cả người dùng để chọn
                $query->where('id', '>', 0);
                break;
            default:
                $query->where('id', '<=', 0);
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
                $this->guiDenTatCaNguoiDung($thongBao);
                break;

            case 'user':
                $this->guiDenKhachHang($thongBao);
                break;

            case 'vip':
                $this->guiDenVip($thongBao);
                break;

            case 'staff':
                $this->guiDenNhanVien($thongBao);
                break;

            case 'admin':
                $this->guiDenQuanTriVien($thongBao);
                break;

            case 'nguoi_dung_cu_the':
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
     * Gửi thông báo đến khách hàng thường.
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
     * Gửi thông báo đến khách hàng VIP.
     */
    private function guiDenVip(ThongBaoPush $thongBao): void
    {
        $vipUsers = NguoiDung::where('vai_tro', 'vip')->select('id')->get();

        foreach ($vipUsers as $vip) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $vip->id,
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
