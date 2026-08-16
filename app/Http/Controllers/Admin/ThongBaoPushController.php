<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreThongBaoPushRequest;
use App\Models\NguoiDung;
use App\Models\ThongBaoPush;
use App\Models\ThongBaoPushNguoiDung;
use App\Models\ThongBaoCaNhan;
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

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $summary = [
            'total' => ThongBaoPush::count(),

            'sent' => ThongBaoPush::where(
                'trang_thai',
                'da_gui'
            )->count(),

            'promo' => ThongBaoPush::where(
                'loai',
                'promo'
            )->count(),

            'today' => ThongBaoPush::whereDate(
                'created_at',
                now()->toDateString()
            )->count(),
        ];

        $hangThanhVienOptions = [
            'member' => 'Member',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
        ];

        $query = ThongBaoPush::with('nguoiTao');

        /*
        |--------------------------------------------------------------------------
        | TÌM KIẾM
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $query->where(
                'tieu_de',
                'like',
                '%' . trim($request->search) . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC LOẠI
        |--------------------------------------------------------------------------
        */
        if ($request->filled('loai')) {
            $query->where(
                'loai',
                $request->input('loai')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC TRẠNG THÁI
        |--------------------------------------------------------------------------
        */
        if ($request->filled('trang_thai')) {
            $query->where(
                'trang_thai',
                $request->input('trang_thai')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC ĐỐI TƯỢNG
        |--------------------------------------------------------------------------
        */
        if ($request->filled('doi_tuong_nhan')) {

            $query->where(
                'doi_tuong_nhan',
                $request->input('doi_tuong_nhan')
            );

            if (
                $request->input('doi_tuong_nhan') === 'hang_thanh_vien'
                && $request->filled('hang_thanh_vien')
            ) {
                $query->where(
                    'hang_thanh_vien',
                    $request->input('hang_thanh_vien')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        */
        if (
            $request->input('doi_tuong_nhan') === 'nguoi_dung_cu_the'
            && $request->filled('nguoi_dung')
        ) {

            $keyword = trim(
                $request->input('nguoi_dung')
            );

            $query->whereExists(function ($subQuery) use ($keyword) {

                $subQuery
                    ->select(DB::raw(1))
                    ->from('thong_bao_push_nguoi_dungs as tbnd')
                    ->join(
                        'nguoi_dungs as nd',
                        'nd.id',
                        '=',
                        'tbnd.nguoi_dung_id'
                    )
                    ->whereColumn(
                        'tbnd.thong_bao_push_id',
                        'thong_bao_pushs.id'
                    )
                    ->where(function ($q) use ($keyword) {

                        $q->where(
                            'nd.ho_ten',
                            'like',
                            '%' . $keyword . '%'
                        )
                            ->orWhere(
                                'nd.email',
                                'like',
                                '%' . $keyword . '%'
                            );
                    });
            });
        }

        $thongBaos = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | NGƯỜI DÙNG TÌM KIẾM
        |--------------------------------------------------------------------------
        */
        $nguoiDungTimKiem = collect();

        if (
            $request->input('doi_tuong_nhan') === 'nguoi_dung_cu_the'
            && $request->filled('nguoi_dung')
        ) {

            $keyword = trim(
                $request->input('nguoi_dung')
            );

            $nguoiDungTimKiem = NguoiDung::query()
                ->whereIn('vai_tro', [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ])
                ->where(function ($q) use ($keyword) {

                    $q->where(
                        'ho_ten',
                        'like',
                        '%' . $keyword . '%'
                    )
                        ->orWhere(
                            'email',
                            'like',
                            '%' . $keyword . '%'
                        );
                })
                ->select(
                    'id',
                    'ho_ten',
                    'email'
                )
                ->get();
        }

        return view(
            'admin.thong-bao-push.index',
            compact(
                'thongBaos',
                'summary',
                'hangThanhVienOptions',
                'nguoiDungTimKiem'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        $loaiOptions = [
            'info' => 'Thông tin',
            'warning' => 'Cảnh báo',
            'promo' => 'Khuyến mãi',
            'system' => 'Hệ thống',
        ];

        $doiTuongOptions = [
            'all' => 'Tất cả người dùng',
            'khach_hang' => 'Khách hàng',
            'nhan_vien' => 'Nhân viên',
            'quan_ly' => 'Quản lý',
            'hang_thanh_vien' => 'Theo hạng thành viên',
            'nguoi_dung_cu_the' => 'Người dùng cụ thể',
        ];

        $audienceCounts = [
            'all' => NguoiDung::whereIn(
                'vai_tro',
                [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ]
            )->count(),

            'khach_hang' => NguoiDung::where(
                'vai_tro',
                'khach_hang'
            )->count(),

            'nhan_vien' => NguoiDung::where(
                'vai_tro',
                'nhan_vien'
            )->count(),

            'quan_ly' => NguoiDung::where(
                'vai_tro',
                'quan_ly'
            )->count(),

            'hang_thanh_vien' => NguoiDung::whereHas(
                'thanhVien'
            )->count(),

            'nguoi_dung_cu_the' => NguoiDung::whereIn(
                'vai_tro',
                [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ]
            )->count(),
        ];

        $hangThanhVienOptions = [
            'member' => 'Member',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
        ];

        return view(
            'admin.thong-bao-push.create',
            compact(
                'loaiOptions',
                'doiTuongOptions',
                'audienceCounts',
                'hangThanhVienOptions'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(
        StoreThongBaoPushRequest $request
    ): RedirectResponse {

        $validated = $request->validated();

        $action = $request->input('action');

        if (!in_array($action, ['draft', 'send'], true)) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Vui lòng chọn Lưu nháp hoặc Gửi thông báo.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA HẠNG THÀNH VIÊN
    |--------------------------------------------------------------------------
    */

        if (
            $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
            && empty($validated['hang_thanh_vien'])
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'hang_thanh_vien' =>
                    'Vui lòng chọn hạng thành viên.',
                ]);
        }

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA NGƯỜI DÙNG CỤ THỂ
    |--------------------------------------------------------------------------
    */

        if (
            $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
            && empty($validated['nguoi_dung_cu_the'])
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'nguoi_dung_cu_the' =>
                    'Vui lòng chọn người dùng.',
                ]);
        }

        /*
    |--------------------------------------------------------------------------
    | XÁC ĐỊNH USER CỤ THỂ
    |--------------------------------------------------------------------------
    */

        $nguoiDungCuThe = null;

        if (
            $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
            && !empty($validated['nguoi_dung_cu_the'])
        ) {
            $nguoiDungCuThe = (int) $validated['nguoi_dung_cu_the'];

            /*
        | Kiểm tra user có tồn tại
        */

            $user = NguoiDung::whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])
                ->find($nguoiDungCuThe);

            if (!$user) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nguoi_dung_cu_the' =>
                        'Người dùng được chọn không tồn tại.',
                    ]);
            }
        }

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | TẠO THÔNG BÁO
        |--------------------------------------------------------------------------
        */

            $thongBao = ThongBaoPush::create([
                'tieu_de' => $validated['tieu_de'],

                'noi_dung' => $validated['noi_dung'],

                'loai' => $validated['loai'],

                'doi_tuong_nhan' => $validated['doi_tuong_nhan'],

                'hang_thanh_vien' =>
                $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
                    ? ($validated['hang_thanh_vien'] ?? null)
                    : null,

                'nguoi_tao_id' => auth()->id(),

                /*
            | Bản nháp = chưa gửi
            */

                'trang_thai' => 'chua_gui',

                'thoi_gian_gui' => null,
            ]);

            /*
        |--------------------------------------------------------------------------
        | LƯU NGƯỜI NHẬN CHO BẢN NHÁP
        |--------------------------------------------------------------------------
        |
        | Lưu recipient để khi EDIT vẫn biết user nào đã được chọn.
        |
        | QUAN TRỌNG:
        | Recipient này KHÔNG có nghĩa là đã gửi.
        | Phía USER phải lọc trang_thai = da_gui.
        |
        */

            if (
                $action === 'draft'
                && $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
                && $nguoiDungCuThe
            ) {

                ThongBaoPushNguoiDung::create([
                    'thong_bao_push_id' => $thongBao->id,

                    'nguoi_dung_id' => $nguoiDungCuThe,

                    'da_doc' => false,

                    'thoi_gian_doc' => null,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | NẾU LƯU NHÁP
        |--------------------------------------------------------------------------
        */

            if ($action === 'draft') {

                DB::commit();

                $this->ghiNhatKy(
                    $request,
                    'Lưu nháp thông báo đẩy',
                    'Quản lý thông báo đẩy',
                    "Lưu nháp thông báo: {$thongBao->tieu_de}",
                    [
                        'id' => $thongBao->id,

                        'tieu_de' => $thongBao->tieu_de,

                        'doi_tuong_nhan' =>
                        $thongBao->doi_tuong_nhan,
                    ]
                );

                return redirect()
                    ->route('admin.thong-bao-push.index')
                    ->with(
                        'success',
                        'Thông báo đã được lưu vào bản nháp.'
                    );
            }

            /*
        |--------------------------------------------------------------------------
        | NẾU GỬI NGAY
        |--------------------------------------------------------------------------
        |
        | Bản ghi vừa tạo chưa có recipient.
        | guiThongBao() sẽ tạo recipient thực tế.
        |
        */

            $this->guiThongBao(
                $thongBao,
                $thongBao->doi_tuong_nhan,
                $nguoiDungCuThe,
                $thongBao->doi_tuong_nhan === 'hang_thanh_vien'
                    ? $thongBao->hang_thanh_vien
                    : null
            );

            /*
        |--------------------------------------------------------------------------
        | ĐÁNH DẤU ĐÃ GỬI
        |--------------------------------------------------------------------------
        */

            $thongBao->update([
                'trang_thai' => 'da_gui',

                'thoi_gian_gui' => now(),
            ]);

            DB::commit();

            /*
        |--------------------------------------------------------------------------
        | GHI NHẬT KÝ
        |--------------------------------------------------------------------------
        */

            $this->ghiNhatKy(
                $request,
                'Gửi thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Gửi thông báo: {$thongBao->tieu_de}",
                [
                    'id' => $thongBao->id,

                    'tieu_de' => $thongBao->tieu_de,

                    'doi_tuong_nhan' =>
                    $thongBao->doi_tuong_nhan,
                ]
            );

            /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO ADMIN
        |--------------------------------------------------------------------------
        */

            AdminNotificationService::push(
                '📤 Đã gửi thông báo đẩy',
                "Đã gửi thông báo: {$thongBao->tieu_de}",
                'success'
            );

            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with(
                    'success',
                    'Thông báo đã được gửi thành công.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error(
                'Lỗi tạo/gửi thông báo đẩy',
                [
                    'message' => $e->getMessage(),

                    'file' => $e->getFile(),

                    'line' => $e->getLine(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể xử lý thông báo: '
                        . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI BẢN NHÁP
    |--------------------------------------------------------------------------
    */
    public function send(
        Request $request,
        ThongBaoPush $thongBao
    ): RedirectResponse {

        /*
    |--------------------------------------------------------------------------
    | CHỈ ĐƯỢC GỬI BẢN NHÁP
    |--------------------------------------------------------------------------
    */

        if ($thongBao->trang_thai !== 'chua_gui') {

            return back()
                ->with(
                    'error',
                    'Thông báo này đã được gửi hoặc không thể gửi.'
                );
        }

        DB::beginTransaction();

        try {

            $nguoiDungCuThe = null;

            /*
        |--------------------------------------------------------------------------
        | LẤY USER CỤ THỂ ĐÃ LƯU TRONG BẢN NHÁP
        |--------------------------------------------------------------------------
        */

            if (
                $thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the'
            ) {

                $nguoiDungCuThe =
                    ThongBaoPushNguoiDung::where(
                        'thong_bao_push_id',
                        $thongBao->id
                    )
                    ->value('nguoi_dung_id');

                if (!$nguoiDungCuThe) {

                    throw new \Exception(
                        'Bản nháp chưa có người dùng nhận thông báo.'
                    );
                }

                /*
            | Kiểm tra user vẫn còn tồn tại
            */

                $user = NguoiDung::whereIn('vai_tro', [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ])
                    ->find($nguoiDungCuThe);

                if (!$user) {

                    throw new \Exception(
                        'Người dùng nhận thông báo không còn tồn tại.'
                    );
                }

                /*
            | Xóa recipient tạm của bản nháp.
            |
            | Sau đó guiThongBao() sẽ tạo recipient chính thức.
            */

                ThongBaoPushNguoiDung::where(
                    'thong_bao_push_id',
                    $thongBao->id
                )->delete();
            }

            /*
        |--------------------------------------------------------------------------
        | GỬI THÔNG BÁO
        |--------------------------------------------------------------------------
        */

            $this->guiThongBao(
                $thongBao,
                $thongBao->doi_tuong_nhan,
                $nguoiDungCuThe,
                $thongBao->doi_tuong_nhan === 'hang_thanh_vien'
                    ? $thongBao->hang_thanh_vien
                    : null
            );

            /*
        |--------------------------------------------------------------------------
        | ĐÁNH DẤU ĐÃ GỬI
        |--------------------------------------------------------------------------
        */

            $thongBao->update([
                'trang_thai' => 'da_gui',

                'thoi_gian_gui' => now(),
            ]);

            DB::commit();

            /*
        |--------------------------------------------------------------------------
        | GHI NHẬT KÝ
        |--------------------------------------------------------------------------
        */

            $this->ghiNhatKy(
                $request,
                'Gửi thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Gửi thông báo: {$thongBao->tieu_de}",
                [
                    'id' => $thongBao->id,

                    'tieu_de' => $thongBao->tieu_de,

                    'doi_tuong_nhan' =>
                    $thongBao->doi_tuong_nhan,
                ]
            );

            AdminNotificationService::push(
                '📤 Đã gửi thông báo đẩy',
                "Đã gửi thông báo: {$thongBao->tieu_de}",
                'success'
            );

            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with(
                    'success',
                    'Thông báo đã được gửi thành công.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error(
                'Lỗi gửi thông báo đẩy từ bản nháp',
                [
                    'thong_bao_push_id' =>
                    $thongBao->id,

                    'message' =>
                    $e->getMessage(),

                    'file' =>
                    $e->getFile(),

                    'line' =>
                    $e->getLine(),
                ]
            );

            return back()
                ->with(
                    'error',
                    'Không thể gửi thông báo: '
                        . $e->getMessage()
                );
        }
    }
    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(
        ThongBaoPush $thongBao
    ): View {

        $thongBao->load([
            'nguoiTao',
        ]);

        $recipientQuery =
            ThongBaoPushNguoiDung::with('nguoiDung')
            ->where(
                'thong_bao_push_id',
                $thongBao->id
            );

        $recipientCount =
            $recipientQuery->count();

        $nguoiNhanList = collect();

        if (
            $thongBao->doi_tuong_nhan
            === 'nguoi_dung_cu_the'
        ) {

            $nguoiNhanList =
                $recipientQuery
                ->get()
                ->pluck('nguoiDung')
                ->filter();
        }

        $thongBaoPush = $thongBao;

        return view(
            'admin.thong-bao-push.show',
            compact(
                'thongBaoPush',
                'nguoiNhanList',
                'recipientCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(
        ThongBaoPush $thongBao
    ): View {

        $loaiOptions = [
            'info' => 'Thông tin',
            'warning' => 'Cảnh báo',
            'promo' => 'Khuyến mãi',
            'system' => 'Hệ thống',
        ];

        $doiTuongOptions = [
            'all' => 'Tất cả người dùng',
            'khach_hang' => 'Khách hàng',
            'nhan_vien' => 'Nhân viên',
            'quan_ly' => 'Quản lý',
            'hang_thanh_vien' => 'Theo hạng thành viên',
            'nguoi_dung_cu_the' => 'Người dùng cụ thể',
        ];

        $hangThanhVienOptions = [
            'member' => 'Member',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
        ];

        $audienceCounts = [
            'all' => NguoiDung::whereIn(
                'vai_tro',
                [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ]
            )->count(),

            'khach_hang' => NguoiDung::where(
                'vai_tro',
                'khach_hang'
            )->count(),

            'nhan_vien' => NguoiDung::where(
                'vai_tro',
                'nhan_vien'
            )->count(),

            'quan_ly' => NguoiDung::where(
                'vai_tro',
                'quan_ly'
            )->count(),

            'hang_thanh_vien' => NguoiDung::whereHas(
                'thanhVien'
            )->count(),

            'nguoi_dung_cu_the' => NguoiDung::whereIn(
                'vai_tro',
                [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ]
            )->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | USER ĐANG CHỌN
        |--------------------------------------------------------------------------
        */

        $selectedUser = null;

        if (
            $thongBao->doi_tuong_nhan
            === 'nguoi_dung_cu_the'
        ) {

            $selectedUser =
                ThongBaoPushNguoiDung::with('nguoiDung')
                ->where(
                    'thong_bao_push_id',
                    $thongBao->id
                )
                ->first();

            $selectedUser =
                $selectedUser?->nguoiDung;
        }

        $thongBaoPush = $thongBao;

        return view(
            'admin.thong-bao-push.edit',
            compact(
                'thongBaoPush',
                'loaiOptions',
                'doiTuongOptions',
                'hangThanhVienOptions',
                'audienceCounts',
                'selectedUser'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        ThongBaoPush $thongBao
    ): RedirectResponse {

        /*
    |--------------------------------------------------------------------------
    | VALIDATE
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate(
            [
                'tieu_de' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'noi_dung' => [
                    'required',
                    'string',
                    'max:1000',
                ],

                'loai' => [
                    'required',
                    'in:info,warning,promo,system',
                ],

                'doi_tuong_nhan' => [
                    'required',
                    'in:all,khach_hang,nhan_vien,quan_ly,hang_thanh_vien,nguoi_dung_cu_the',
                ],

                'hang_thanh_vien' => [
                    'nullable',
                    'in:member,silver,gold,platinum',
                ],

                'nguoi_dung_cu_the' => [
                    'nullable',
                    'integer',
                    'exists:nguoi_dungs,id',
                ],
            ],
            [
                'tieu_de.required' =>
                'Vui lòng nhập tiêu đề thông báo.',

                'tieu_de.max' =>
                'Tiêu đề thông báo không được vượt quá :max ký tự.',

                'noi_dung.required' =>
                'Vui lòng nhập nội dung thông báo.',

                'noi_dung.max' =>
                'Nội dung thông báo không được vượt quá :max ký tự.',

                'loai.required' =>
                'Vui lòng chọn loại thông báo.',

                'loai.in' =>
                'Loại thông báo không hợp lệ.',

                'doi_tuong_nhan.required' =>
                'Vui lòng chọn đối tượng nhận.',

                'doi_tuong_nhan.in' =>
                'Đối tượng nhận không hợp lệ.',

                'hang_thanh_vien.in' =>
                'Hạng thành viên không hợp lệ.',

                'nguoi_dung_cu_the.integer' =>
                'Người dùng không hợp lệ.',

                'nguoi_dung_cu_the.exists' =>
                'Người dùng được chọn không tồn tại.',
            ]
        );

        /*
    |--------------------------------------------------------------------------
    | ACTION
    |--------------------------------------------------------------------------
    */

        $action = $request->input('action');

        if (!in_array($action, ['draft', 'send'], true)) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Vui lòng chọn Lưu nháp hoặc Gửi thông báo.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA HẠNG
    |--------------------------------------------------------------------------
    */

        if (
            $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
            && empty($validated['hang_thanh_vien'])
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'hang_thanh_vien' =>
                    'Vui lòng chọn hạng thành viên.',
                ]);
        }

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA USER CỤ THỂ
    |--------------------------------------------------------------------------
    */

        if (
            $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
            && empty($validated['nguoi_dung_cu_the'])
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'nguoi_dung_cu_the' =>
                    'Vui lòng chọn người dùng.',
                ]);
        }

        /*
    |--------------------------------------------------------------------------
    | CHỈ CHO PHÉP SỬA BẢN NHÁP
    |--------------------------------------------------------------------------
    */

        if (
            $thongBao->trang_thai !== 'chua_gui'
            && $action === 'draft'
        ) {

            return back()
                ->with(
                    'error',
                    'Chỉ có thể lưu chỉnh sửa đối với thông báo chưa gửi.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | USER CỤ THỂ
    |--------------------------------------------------------------------------
    */

        $nguoiDungCuThe = null;

        if (
            $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
            && !empty($validated['nguoi_dung_cu_the'])
        ) {

            $nguoiDungCuThe =
                (int) $validated['nguoi_dung_cu_the'];

            $user = NguoiDung::whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])
                ->find($nguoiDungCuThe);

            if (!$user) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'nguoi_dung_cu_the' =>
                        'Người dùng được chọn không tồn tại.',
                    ]);
            }
        }

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | CẬP NHẬT THÔNG BÁO
        |--------------------------------------------------------------------------
        */

            $thongBao->update([
                'tieu_de' =>
                $validated['tieu_de'],

                'noi_dung' =>
                $validated['noi_dung'],

                'loai' =>
                $validated['loai'],

                'doi_tuong_nhan' =>
                $validated['doi_tuong_nhan'],

                'hang_thanh_vien' =>
                $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
                    ? ($validated['hang_thanh_vien'] ?? null)
                    : null,
            ]);

            /*
        |--------------------------------------------------------------------------
        | XÓA RECIPIENT CŨ
        |--------------------------------------------------------------------------
        |
        | Sau đó:
        |
        | - draft + user cụ thể => tạo lại recipient để edit giữ được user
        | - send => xóa recipient cũ, guiThongBao() tạo recipient thật
        |
        */

            ThongBaoPushNguoiDung::where(
                'thong_bao_push_id',
                $thongBao->id
            )->delete();

            /*
        |--------------------------------------------------------------------------
        | LƯU NHÁP
        |--------------------------------------------------------------------------
        */

            if ($action === 'draft') {

                if (
                    $validated['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
                    && $nguoiDungCuThe
                ) {

                    ThongBaoPushNguoiDung::create([
                        'thong_bao_push_id' =>
                        $thongBao->id,

                        'nguoi_dung_id' =>
                        $nguoiDungCuThe,

                        'da_doc' =>
                        false,

                        'thoi_gian_doc' =>
                        null,
                    ]);
                }

                $thongBao->update([
                    'trang_thai' =>
                    'chua_gui',

                    'thoi_gian_gui' =>
                    null,
                ]);

                DB::commit();

                $this->ghiNhatKy(
                    $request,
                    'Cập nhật thông báo đẩy',
                    'Quản lý thông báo đẩy',
                    "Lưu nháp thông báo: {$thongBao->tieu_de}",
                    [
                        'id' =>
                        $thongBao->id,

                        'tieu_de' =>
                        $thongBao->tieu_de,

                        'doi_tuong_nhan' =>
                        $thongBao->doi_tuong_nhan,
                    ]
                );

                return redirect()
                    ->route('admin.thong-bao-push.index')
                    ->with(
                        'success',
                        'Thông báo đã được cập nhật và lưu vào bản nháp.'
                    );
            }

            /*
        |--------------------------------------------------------------------------
        | GỬI
        |--------------------------------------------------------------------------
        */

            $this->guiThongBao(
                $thongBao,
                $validated['doi_tuong_nhan'],
                $nguoiDungCuThe,
                $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
                    ? ($validated['hang_thanh_vien'] ?? null)
                    : null
            );

            /*
        |--------------------------------------------------------------------------
        | ĐÁNH DẤU ĐÃ GỬI
        |--------------------------------------------------------------------------
        */

            $thongBao->update([
                'trang_thai' =>
                'da_gui',

                'thoi_gian_gui' =>
                now(),
            ]);

            DB::commit();

            $this->ghiNhatKy(
                $request,
                'Gửi thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Gửi thông báo: {$thongBao->tieu_de}",
                [
                    'id' =>
                    $thongBao->id,

                    'tieu_de' =>
                    $thongBao->tieu_de,

                    'doi_tuong_nhan' =>
                    $thongBao->doi_tuong_nhan,
                ]
            );

            AdminNotificationService::push(
                '📤 Đã gửi thông báo đẩy',
                "Đã gửi thông báo: {$thongBao->tieu_de}",
                'success'
            );

            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with(
                    'success',
                    'Thông báo đã được cập nhật và gửi thành công.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error(
                'Lỗi cập nhật/gửi thông báo đẩy',
                [
                    'thong_bao_push_id' =>
                    $thongBao->id,

                    'message' =>
                    $e->getMessage(),

                    'file' =>
                    $e->getFile(),

                    'line' =>
                    $e->getLine(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể cập nhật/gửi thông báo: '
                        . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(
        Request $request,
        ThongBaoPush $thongBao
    ): RedirectResponse {

        $user = auth()->user();

        if (
            !$user->hasRole('Quản trị viên')
            &&
            !$user->hasRole('Quản lý hệ thống')
        ) {

            return back()
                ->with(
                    'error',
                    'Bạn không có quyền xóa thông báo đẩy.'
                );
        }

        $tieuDe = $thongBao->tieu_de;
        $thongBaoId = $thongBao->id;

        try {

            DB::transaction(function () use ($thongBao) {

                $thongBao->delete();
            });

            $this->ghiNhatKy(
                $request,
                'Xóa thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Xóa thông báo: {$tieuDe}",
                [
                    'id' =>
                    $thongBaoId,

                    'tieu_de' =>
                    $tieuDe,
                ]
            );

            AdminNotificationService::push(
                '🗑️ Xóa thông báo đẩy',
                "Đã xóa thông báo: {$tieuDe}",
                'warning'
            );

            return redirect()
                ->route(
                    'admin.thong-bao-push.index'
                )
                ->with(
                    'success',
                    'Thông báo đẩy đã được xóa thành công.'
                );
        } catch (\Exception $e) {

            return back()
                ->with(
                    'error',
                    'Không thể xóa thông báo: '
                        . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX - USER THEO VAI TRÒ
    |--------------------------------------------------------------------------
    */
    public function getUsersByRole(
        Request $request
    ): JsonResponse {

        $role = $request->get('role');

        $query = NguoiDung::query();

        switch ($role) {

            case 'khach_hang':

                $query->where(
                    'vai_tro',
                    'khach_hang'
                );

                break;

            case 'nhan_vien':

                $query->where(
                    'vai_tro',
                    'nhan_vien'
                );

                break;

            case 'quan_ly':

                $query->where(
                    'vai_tro',
                    'quan_ly'
                );

                break;

            case 'nguoi_dung_cu_the':

                $query->where(
                    'id',
                    '>',
                    0
                );

                break;

            default:

                $query->where(
                    'id',
                    '<=',
                    0
                );

                break;
        }

        $users = $query
            ->select(
                'id',
                'ho_ten',
                'email'
            )
            ->get();

        return response()->json($users);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX - TÌM NGƯỜI DÙNG
    |--------------------------------------------------------------------------
    */
    public function timNguoiDung(
        Request $request
    ): JsonResponse {

        $keyword = trim(
            $request->input('keyword', '')
        );

        if ($keyword === '') {
            return response()->json([]);
        }

        $users = NguoiDung::query()
            ->whereIn(
                'vai_tro',
                [
                    'khach_hang',
                    'nhan_vien',
                    'quan_ly',
                ]
            )
            ->where(function ($query) use ($keyword) {

                $query->where(
                    'ho_ten',
                    'like',
                    '%' . $keyword . '%'
                )
                    ->orWhere(
                        'email',
                        'like',
                        '%' . $keyword . '%'
                    );
            })
            ->select(
                'id',
                'ho_ten',
                'email'
            )
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI THÔNG BÁO
    |--------------------------------------------------------------------------
    */
    private function guiThongBao(
        ThongBaoPush $thongBao,
        string $doiTuongNhan,
        ?int $nguoiDungCuThe = null,
        ?string $hangThanhVien = null
    ): void {

        switch ($doiTuongNhan) {

            case 'all':

                $this->guiDenTatCaNguoiDung($thongBao);

                break;

            case 'khach_hang':

                $this->guiDenKhachHang($thongBao);

                break;

            case 'nhan_vien':

                $this->guiDenNhanVien($thongBao);

                break;

            case 'quan_ly':

                $this->guiDenQuanLy($thongBao);

                break;

            case 'hang_thanh_vien':

                if (!$hangThanhVien) {

                    throw new \Exception(
                        'Chưa chọn hạng thành viên.'
                    );
                }

                $this->guiDenHangThanhVien(
                    $thongBao,
                    $hangThanhVien
                );

                break;

            case 'nguoi_dung_cu_the':

                if (!$nguoiDungCuThe) {

                    throw new \Exception(
                        'Chưa xác định được người dùng nhận thông báo.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | TẠO RECIPIENT THỰC TẾ KHI GỬI
            |--------------------------------------------------------------------------
            */

                $recipient =
                    ThongBaoPushNguoiDung::firstOrCreate(
                        [
                            'thong_bao_push_id' =>
                            $thongBao->id,

                            'nguoi_dung_id' =>
                            $nguoiDungCuThe,
                        ],
                        [
                            'da_doc' =>
                            false,

                            'thoi_gian_doc' =>
                            null,
                        ]
                    );

                /*
            |--------------------------------------------------------------------------
            | NHÂN VIÊN -> CHUÔNG CÁ NHÂN
            |--------------------------------------------------------------------------
            */

                $user = NguoiDung::find(
                    $nguoiDungCuThe
                );

                if (
                    $user
                    && $user->vai_tro === 'nhan_vien'
                ) {

                    ThongBaoCaNhan::create([
                        'nguoi_dung_id' =>
                        $user->id,

                        'tieu_de' =>
                        $thongBao->tieu_de,

                        'noi_dung' =>
                        $thongBao->noi_dung,

                        'loai_thong_bao' =>
                        'he_thong',

                        'duong_dan' =>
                        null,

                        'da_doc' =>
                        false,

                        'doc_luc' =>
                        null,
                    ]);
                }

                break;

            default:

                throw new \Exception(
                    'Đối tượng nhận thông báo không hợp lệ.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI THEO HẠNG
    |--------------------------------------------------------------------------
    */
    private function guiDenHangThanhVien(
        ThongBaoPush $thongBao,
        string $hangThanhVien
    ): void {

        $users = NguoiDung::whereHas(
            'thanhVien',
            function ($query) use ($hangThanhVien) {

                $query->where(
                    'hang_thanh_vien',
                    $hangThanhVien
                );
            }
        )
            ->select('id')
            ->get();

        foreach ($users as $user) {

            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' =>
                $thongBao->id,

                'nguoi_dung_id' =>
                $user->id,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI TẤT CẢ
    |--------------------------------------------------------------------------
    */
    private function guiDenTatCaNguoiDung(
        ThongBaoPush $thongBao
    ): void {

        $nguoiDungs = NguoiDung::whereIn(
            'vai_tro',
            [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ]
        )
            ->select('id')
            ->get();

        foreach ($nguoiDungs as $nguoiDung) {

            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' =>
                $thongBao->id,

                'nguoi_dung_id' =>
                $nguoiDung->id,
            ]);

            /*
            | Nhân viên nhận thêm chuông cá nhân
            */

            if (
                $nguoiDung->vai_tro === 'nhan_vien'
            ) {

                ThongBaoCaNhan::create([
                    'nguoi_dung_id' =>
                    $nguoiDung->id,

                    'tieu_de' =>
                    $thongBao->tieu_de,

                    'noi_dung' =>
                    $thongBao->noi_dung,

                    'loai_thong_bao' =>
                    'he_thong',

                    'duong_dan' =>
                    null,

                    'da_doc' =>
                    false,

                    'doc_luc' =>
                    null,
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */
    private function guiDenKhachHang(
        ThongBaoPush $thongBao
    ): void {

        $users = NguoiDung::where(
            'vai_tro',
            'khach_hang'
        )
            ->select('id')
            ->get();

        foreach ($users as $user) {

            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' =>
                $thongBao->id,

                'nguoi_dung_id' =>
                $user->id,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI NHÂN VIÊN
    |--------------------------------------------------------------------------
    */
    private function guiDenNhanVien(
        ThongBaoPush $thongBao
    ): void {

        $users = NguoiDung::where(
            'vai_tro',
            'nhan_vien'
        )
            ->select('id')
            ->get();

        foreach ($users as $user) {

            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' =>
                $thongBao->id,

                'nguoi_dung_id' =>
                $user->id,
            ]);

            ThongBaoCaNhan::create([
                'nguoi_dung_id' =>
                $user->id,

                'tieu_de' =>
                $thongBao->tieu_de,

                'noi_dung' =>
                $thongBao->noi_dung,

                'loai_thong_bao' =>
                'he_thong',

                'duong_dan' =>
                null,

                'da_doc' =>
                false,

                'doc_luc' =>
                null,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GỬI QUẢN LÝ
    |--------------------------------------------------------------------------
    */
    private function guiDenQuanLy(
        ThongBaoPush $thongBao
    ): void {

        $users = NguoiDung::where(
            'vai_tro',
            'quan_ly'
        )
            ->select('id')
            ->get();

        foreach ($users as $user) {

            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' =>
                $thongBao->id,

                'nguoi_dung_id' =>
                $user->id,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | THÙNG RÁC
    |--------------------------------------------------------------------------
    */
    public function trash(
        Request $request
    ): View {

        $query = ThongBaoPush::onlyTrashed()
            ->with('nguoiTao')
            ->latest('deleted_at');

        if ($request->filled('search')) {

            $search = trim(
                $request->input('search')
            );

            $query->where(
                'tieu_de',
                'like',
                '%' . $search . '%'
            );
        }

        if ($request->filled('loai')) {

            $query->where(
                'loai',
                $request->input('loai')
            );
        }

        if ($request->filled('trang_thai')) {

            $query->where(
                'trang_thai',
                $request->input('trang_thai')
            );
        }

        if ($request->filled('doi_tuong_nhan')) {

            $query->where(
                'doi_tuong_nhan',
                $request->input('doi_tuong_nhan')
            );
        }

        if (
            $request->input('doi_tuong_nhan')
            === 'hang_thanh_vien'
            && $request->filled('hang_thanh_vien')
        ) {

            $query->where(
                'hang_thanh_vien',
                $request->input('hang_thanh_vien')
            );
        }

        if (
            $request->input('doi_tuong_nhan')
            === 'nguoi_dung_cu_the'
            && $request->filled('nguoi_dung')
        ) {

            $keyword = trim(
                $request->input('nguoi_dung')
            );

            $query->whereHas(
                'nguoiNhans',
                function ($q) use ($keyword) {

                    $q->whereHas(
                        'nguoiDung',
                        function ($userQuery) use ($keyword) {

                            $userQuery
                                ->where(
                                    'ho_ten',
                                    'like',
                                    '%' . $keyword . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $keyword . '%'
                                );
                        }
                    );
                }
            );
        }

        $thongBaos = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.thong-bao-push.trash',
            compact('thongBaos')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */
    public function restore(
        int $thongBao
    ): RedirectResponse {

        $record = ThongBaoPush::onlyTrashed()
            ->findOrFail($thongBao);

        $record->restore();

        return redirect()
            ->route(
                'admin.thong-bao-push.trash'
            )
            ->with(
                'success',
                'Khôi phục thông báo thành công.'
            );
    }
}
