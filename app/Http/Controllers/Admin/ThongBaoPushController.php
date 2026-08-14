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

        // =====================================================
        // TÌM KIẾM TIÊU ĐỀ
        // =====================================================
        if ($request->filled('search')) {
            $query->where(
                'tieu_de',
                'like',
                '%' . trim($request->search) . '%'
            );
        }

        // =====================================================
        // LỌC LOẠI THÔNG BÁO
        // =====================================================
        if ($request->filled('loai')) {
            $query->where(
                'loai',
                $request->input('loai')
            );
        }

        // =====================================================
        // LỌC TRẠNG THÁI
        // =====================================================
        if ($request->filled('trang_thai')) {
            $query->where(
                'trang_thai',
                $request->input('trang_thai')
            );
        }

        // =====================================================
        // LỌC ĐỐI TƯỢNG NHẬN
        // =====================================================
        if ($request->filled('doi_tuong_nhan')) {

            $query->where(
                'doi_tuong_nhan',
                $request->input('doi_tuong_nhan')
            );

            // -------------------------------------------------
            // LỌC HẠNG THÀNH VIÊN
            // -------------------------------------------------
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

        // =====================================================
        // LỌC NGƯỜI DÙNG CỤ THỂ
        // =====================================================
        if (
            $request->input('doi_tuong_nhan') === 'nguoi_dung_cu_the'
            && $request->filled('nguoi_dung')
        ) {

            $keyword = trim(
                $request->input('nguoi_dung')
            );

            $query->whereExists(function ($subQuery) use ($keyword) {

                $subQuery->select(DB::raw(1))
                    ->from(
                        'thong_bao_push_nguoi_dungs as tbnd'
                    )
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

        // =====================================================
        // PHÂN TRANG
        // =====================================================
        $thongBaos = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // =====================================================
        // TÌM NGƯỜI DÙNG CỤ THỂ
        // =====================================================
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

        // =====================================================
        // TRẢ VỀ VIEW
        // =====================================================
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



    /**
     * Form tạo mới thông báo đẩy.
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
            'all' => NguoiDung::whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])->count(),

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

            'nguoi_dung_cu_the' => NguoiDung::whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])->count(),
        ];

        $hangThanhVienOptions = [
            'member' => 'Member',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
        ];
        return view('admin.thong-bao-push.create', compact(
            'loaiOptions',
            'doiTuongOptions',
            'audienceCounts',
            'hangThanhVienOptions'
        ));
    }

    public function store(StoreThongBaoPushRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $thongBao = ThongBaoPush::create([
                'tieu_de' => $validated['tieu_de'],
                'noi_dung' => $validated['noi_dung'],
                'loai' => $validated['loai'],
                'doi_tuong_nhan' => $validated['doi_tuong_nhan'],
                'hang_thanh_vien' => $validated['hang_thanh_vien'] ?? null,
                'nguoi_tao_id' => auth()->id(),
                'trang_thai' => 'chua_gui',
                'thoi_gian_gui' => null,
            ]);

            /*
        |--------------------------------------------------------------------------
        | LƯU NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        */

            if (
                $thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the'
                && !empty($validated['nguoi_dung_cu_the'])
            ) {
                ThongBaoPushNguoiDung::create([
                    'thong_bao_push_id' => $thongBao->id,
                    'nguoi_dung_id' => $validated['nguoi_dung_cu_the'],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | LƯU BẢN NHÁP
        |--------------------------------------------------------------------------
        */

            if ($request->input('action') === 'draft') {
                DB::commit();

                return redirect()
                    ->route('admin.thong-bao-push.index')
                    ->with(
                        'success',
                        'Thông báo đã được lưu vào bản nháp.'
                    );
            }

            /*
        |--------------------------------------------------------------------------
        | GỬI NGAY
        |--------------------------------------------------------------------------
        |
        | QUAN TRỌNG:
        | guiThongBao() sẽ xử lý người nhận.
        | Với nguoi_dung_cu_the, bản ghi đã được lưu ở trên
        | nên không tạo thêm lần nữa.
        |
        */

            $this->guiThongBao(
                $thongBao,
                $thongBao->doi_tuong_nhan,
                $thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the'
                    ? (int) $validated['nguoi_dung_cu_the']
                    : null,
                $thongBao->doi_tuong_nhan === 'hang_thanh_vien'
                    ? ($thongBao->hang_thanh_vien ?? null)
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
                    'doi_tuong_nhan' => $thongBao->doi_tuong_nhan,
                ]
            );

            /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO ADMIN
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

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Không thể xử lý thông báo: ' . $e->getMessage()
                )
                ->withInput();
        }
    }
    /**
     * Gửi thông báo đẩy từ bản nháp.
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
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Thông báo này đã được gửi hoặc không thể gửi.'
                );
        }

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | LẤY NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        */

            $nguoiDungCuThe = null;

            if ($thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the') {

                $nguoiDungCuThe = ThongBaoPushNguoiDung::where(
                    'thong_bao_push_id',
                    $thongBao->id
                )
                    ->value('nguoi_dung_id');

                if (!$nguoiDungCuThe) {
                    throw new \Exception(
                        'Không tìm thấy người dùng nhận thông báo.'
                    );
                }
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
                    'doi_tuong_nhan' => $thongBao->doi_tuong_nhan,
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
                'Lỗi gửi thông báo đẩy từ bản nháp',
                [
                    'thong_bao_push_id' => $thongBao->id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Không thể gửi thông báo: ' . $e->getMessage()
                );
        }
    }
    /**
     * Xem chi tiết thông báo đẩy.
     */
    public function show(ThongBaoPush $thongBao): View
    {
        $thongBao->load([
            'nguoiTao',
        ]);

        $recipientQuery = ThongBaoPushNguoiDung::with('nguoiDung')
            ->where('thong_bao_push_id', $thongBao->id);

        $recipientCount = $recipientQuery->count();

        $nguoiNhanList = collect();

        if ($thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the') {
            $nguoiNhanList = $recipientQuery
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

                'tieu_de.string' =>
                'Tiêu đề thông báo phải là chuỗi.',

                'tieu_de.max' =>
                'Tiêu đề thông báo không được vượt quá :max ký tự.',

                'noi_dung.required' =>
                'Vui lòng nhập nội dung thông báo.',

                'noi_dung.string' =>
                'Nội dung thông báo phải là chuỗi.',

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
    | KIỂM TRA HẠNG THÀNH VIÊN
    |--------------------------------------------------------------------------
    */

        if (
            $validated['doi_tuong_nhan'] === 'hang_thanh_vien'
            && empty($validated['hang_thanh_vien'])
        ) {
            return back()
                ->withErrors([
                    'hang_thanh_vien' =>
                    'Vui lòng chọn hạng thành viên.',
                ])
                ->withInput();
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
                ->withErrors([
                    'nguoi_dung_cu_the' =>
                    'Vui lòng chọn người dùng.',
                ])
                ->withInput();
        }


        /*
    |--------------------------------------------------------------------------
    | LẤY ACTION
    |--------------------------------------------------------------------------
    |
    | edit.blade.php:
    |
    | Lưu nháp:
    | name="action" value="draft"
    |
    | Gửi:
    | name="action" value="send"
    |
    */

        $action = $request->input('action', 'send');


        /*
    |--------------------------------------------------------------------------
    | TRANSACTION
    |--------------------------------------------------------------------------
    */

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | XÁC ĐỊNH NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        */

            $nguoiDungCuThe = null;

            if (
                $validated['doi_tuong_nhan']
                === 'nguoi_dung_cu_the'
            ) {
                $nguoiDungCuThe = (int) $validated['nguoi_dung_cu_the'];
            }


            /*
        |--------------------------------------------------------------------------
        | CẬP NHẬT THÔNG BÁO
        |--------------------------------------------------------------------------
        */

            $thongBao->update([
                'tieu_de' => $validated['tieu_de'],

                'noi_dung' => $validated['noi_dung'],

                'loai' => $validated['loai'],

                'doi_tuong_nhan' =>
                $validated['doi_tuong_nhan'],

                'hang_thanh_vien' =>
                $validated['doi_tuong_nhan']
                    === 'hang_thanh_vien'
                    ? ($validated['hang_thanh_vien'] ?? null)
                    : null,
            ]);


            /*
        |--------------------------------------------------------------------------
        | XÓA NGƯỜI NHẬN CŨ
        |--------------------------------------------------------------------------
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

                /*
            | Nếu là người dùng cụ thể thì lưu lại người nhận
            */
                if (
                    $validated['doi_tuong_nhan']
                    === 'nguoi_dung_cu_the'
                    && $nguoiDungCuThe
                ) {

                    ThongBaoPushNguoiDung::create([
                        'thong_bao_push_id' =>
                        $thongBao->id,

                        'nguoi_dung_id' =>
                        $nguoiDungCuThe,
                    ]);
                }


                /*
            | Trạng thái bản nháp
            */
                $thongBao->update([
                    'trang_thai' => 'chua_gui',

                    'thoi_gian_gui' => null,
                ]);


                DB::commit();


                /*
            |--------------------------------------------------------------------------
            | LOG
            |--------------------------------------------------------------------------
            */

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
        | GỬI THÔNG BÁO
        |--------------------------------------------------------------------------
        |
        | QUAN TRỌNG:
        | Dùng $validated['doi_tuong_nhan']
        | thay vì $thongBao->doi_tuong_nhan
        |
        */

            $this->guiThongBao(
                $thongBao,

                $validated['doi_tuong_nhan'],

                $nguoiDungCuThe,

                $validated['doi_tuong_nhan']
                    === 'hang_thanh_vien'
                    ? ($validated['hang_thanh_vien'] ?? null)
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


            /*
        |--------------------------------------------------------------------------
        | COMMIT
        |--------------------------------------------------------------------------
        */

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
                    'id' =>
                    $thongBao->id,

                    'tieu_de' =>
                    $thongBao->tieu_de,

                    'doi_tuong_nhan' =>
                    $validated['doi_tuong_nhan'],
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


            /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with(
                    'success',
                    'Thông báo đã được cập nhật và gửi thành công.'
                );
        } catch (\Exception $e) {

            /*
        |--------------------------------------------------------------------------
        | ROLLBACK
        |--------------------------------------------------------------------------
        */

            DB::rollBack();


            /*
        |--------------------------------------------------------------------------
        | LOG LỖI
        |--------------------------------------------------------------------------
        */

            \Log::error(
                'Lỗi cập nhật/gửi thông báo đẩy',
                [
                    'thong_bao_push_id' =>
                    $thongBao->id ?? null,

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

    public function edit(ThongBaoPush $thongBao): View
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

        $hangThanhVienOptions = [
            'member' => 'Member',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
        ];

        $audienceCounts = [
            'all' => NguoiDung::whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])->count(),

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

            'nguoi_dung_cu_the' => 0,
        ];

        // Người dùng cụ thể đã chọn
        $selectedUser = null;

        if ($thongBao->doi_tuong_nhan === 'nguoi_dung_cu_the') {

            $selectedUser = ThongBaoPushNguoiDung::with('nguoiDung')
                ->where(
                    'thong_bao_push_id',
                    $thongBao->id
                )
                ->first();

            $selectedUser = $selectedUser?->nguoiDung;
        }

        /*
     * Blade hiện tại của bạn đang sử dụng
     * biến $thongBaoPush.
     *
     * Tạo alias để không phải sửa toàn bộ Blade.
     */
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
    /**
     * Xóa thông báo đẩy.
     */
    public function destroy(
        Request $request,
        ThongBaoPush $thongBao
    ): RedirectResponse {
        // Chỉ cho phép Quản trị viên hoặc Quản lý hệ thống xóa
        $user = auth()->user();

        if (
            !$user->hasRole('Quản trị viên') &&
            !$user->hasRole('Quản lý hệ thống')
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Bạn không có quyền xóa thông báo đẩy.'
                );
        }

        $tieuDe = $thongBao->tieu_de;
        $thongBaoId = $thongBao->id;

        try {
            DB::transaction(function () use ($thongBao) {

                // =====================================================
                // KHÔNG XÓA NGƯỜI NHẬN
                // =====================================================
                // Giữ nguyên ThongBaoPushNguoiDung để User
                // vẫn còn lịch sử thông báo đã nhận.


                // =====================================================
                // SOFT DELETE THÔNG BÁO PUSH
                // =====================================================

                $thongBao->delete();
            });


            // =====================================================
            // GHI NHẬT KÝ
            // =====================================================

            $this->ghiNhatKy(
                $request,
                'Xóa thông báo đẩy',
                'Quản lý thông báo đẩy',
                "Xóa thông báo: {$tieuDe}",
                [
                    'id' => $thongBaoId,
                    'tieu_de' => $tieuDe,
                ]
            );


            // =====================================================
            // THÔNG BÁO ADMIN
            // =====================================================

            AdminNotificationService::push(
                '🗑️ Xóa thông báo đẩy',
                "Đã xóa thông báo: {$tieuDe}",
                'warning'
            );


            return redirect()
                ->route('admin.thong-bao-push.index')
                ->with(
                    'success',
                    'Thông báo đẩy đã được xóa thành công.'
                );
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Không thể xóa thông báo: ' . $e->getMessage()
                );
        }
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
                $query->where('vai_tro', 'khach_hang');
                break;

            case 'nhan_vien':
                $query->where('vai_tro', 'nhan_vien');
                break;

            case 'quan_ly':
                $query->where('vai_tro', 'quan_ly');
                break;

            case 'nguoi_dung_cu_the':
                $query->where('id', '>', 0);
                break;

            default:
                $query->where('id', '<=', 0);
                break;
        }

        $users = $query
            ->select('id', 'ho_ten', 'email')
            ->get();

        return response()->json($users);
    }

    /**
     * Gửi thông báo đến người nhận.
     */
    private function guiThongBao(
        ThongBaoPush $thongBao,
        string $doiTuongNhan,
        ?int $nguoiDungCuThe = null,
        ?string $hangThanhVien = null
    ): void {
        switch ($doiTuongNhan) {

            /*
        |--------------------------------------------------------------------------
        | TẤT CẢ NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        */

            case 'all':
                $this->guiDenTatCaNguoiDung($thongBao);
                break;

            /*
        |--------------------------------------------------------------------------
        | KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

            case 'khach_hang':
                $this->guiDenKhachHang($thongBao);
                break;

            /*
        |--------------------------------------------------------------------------
        | NHÂN VIÊN
        |--------------------------------------------------------------------------
        */

            case 'nhan_vien':
                $this->guiDenNhanVien($thongBao);
                break;

            /*
        |--------------------------------------------------------------------------
        | QUẢN LÝ
        |--------------------------------------------------------------------------
        */

            case 'quan_ly':
                $this->guiDenQuanLy($thongBao);
                break;

            /*
        |--------------------------------------------------------------------------
        | THEO HẠNG THÀNH VIÊN
        |--------------------------------------------------------------------------
        */

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

            /*
        |--------------------------------------------------------------------------
        | NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        |
        | Bản ghi người nhận đã được lưu khi:
        |
        | - Tạo bản nháp
        | - Tạo và gửi ngay
        |
        | Vì vậy ở đây KHÔNG create thêm.
        |
        */

            case 'nguoi_dung_cu_the':

                if (!$nguoiDungCuThe) {
                    throw new \Exception(
                        'Chưa xác định được người dùng nhận thông báo.'
                    );
                }

                $exists = ThongBaoPushNguoiDung::where(
                    'thong_bao_push_id',
                    $thongBao->id
                )
                    ->where(
                        'nguoi_dung_id',
                        $nguoiDungCuThe
                    )
                    ->exists();

                /*
            | Nếu chưa có thì tạo.
            | Nếu đã có thì giữ nguyên.
            */

                if (!$exists) {
                    ThongBaoPushNguoiDung::create([
                        'thong_bao_push_id' => $thongBao->id,
                        'nguoi_dung_id' => $nguoiDungCuThe,
                    ]);
                }

                break;

            default:

                throw new \Exception(
                    'Đối tượng nhận thông báo không hợp lệ.'
                );
        }
    }

    private function guiDenHangThanhVien(
        ThongBaoPush $thongBao,
        string $hangThanhVien
    ): void {
        $users = NguoiDung::whereHas('thanhVien', function ($query) use ($hangThanhVien) {
            $query->where('hang_thanh_vien', $hangThanhVien);
        })
            ->select('id')
            ->get();

        foreach ($users as $user) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $user->id,
            ]);
        }
    }

    private function guiDenTatCaNguoiDung(ThongBaoPush $thongBao): void
    {
        $nguoiDungs = NguoiDung::whereIn('vai_tro', [
            'khach_hang',
            'nhan_vien',
            'quan_ly',
        ])->select('id')->get();

        foreach ($nguoiDungs as $nguoiDung) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $nguoiDung->id,
            ]);
        }
    }

    /**
     */
    private function guiDenKhachHang(ThongBaoPush $thongBao): void
    {
        $users = NguoiDung::where('vai_tro', 'khach_hang')
            ->select('id')
            ->get();

        foreach ($users as $user) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $user->id,
            ]);
        }
    }


    /**
     * Gửi thông báo đến nhân viên.
     */
    private function guiDenNhanVien(ThongBaoPush $thongBao): void
    {
        $users = NguoiDung::where('vai_tro', 'nhan_vien')
            ->select('id')
            ->get();

        foreach ($users as $user) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $user->id,
            ]);
        }
    }

    /**
     * Gửi thông báo đến quản trị viên.
     */
    private function guiDenQuanLy(ThongBaoPush $thongBao): void
    {
        $quanLy = NguoiDung::where('vai_tro', 'quan_ly')
            ->select('id')
            ->get();

        foreach ($quanLy as $user) {
            ThongBaoPushNguoiDung::create([
                'thong_bao_push_id' => $thongBao->id,
                'nguoi_dung_id' => $user->id,
            ]);
        }
    }
    public function timNguoiDung(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $users = NguoiDung::query()
            ->whereIn('vai_tro', [
                'khach_hang',
                'nhan_vien',
                'quan_ly',
            ])
            ->where(function ($query) use ($keyword) {
                $query->where('ho_ten', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            })
            ->select('id', 'ho_ten', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
    public function trash(Request $request)
    {
        $query = ThongBaoPush::onlyTrashed()
            ->with('nguoiTao')
            ->latest('deleted_at');

        // Tìm kiếm theo tiêu đề
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('tieu_de', 'like', '%' . $search . '%');
        }

        // Lọc loại thông báo
        if ($request->filled('loai')) {
            $query->where('loai', $request->loai);
        }

        // Lọc trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Lọc đối tượng nhận
        if ($request->filled('doi_tuong_nhan')) {
            $query->where('doi_tuong_nhan', $request->doi_tuong_nhan);
        }

        // Lọc hạng thành viên
        if (
            $request->doi_tuong_nhan === 'hang_thanh_vien'
            && $request->filled('hang_thanh_vien')
        ) {
            $query->where(
                'hang_thanh_vien',
                $request->hang_thanh_vien
            );
        }

        // Lọc người dùng cụ thể
        if (
            $request->doi_tuong_nhan === 'nguoi_dung_cu_the'
            && $request->filled('nguoi_dung')
        ) {
            $keyword = $request->nguoi_dung;

            $query->whereHas('nguoiNhans', function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        $thongBaos = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.thong-bao-push.trash',
            compact('thongBaos')
        );
    }
    public function restore(ThongBaoPush $thongBao)
{
    $thongBao->restore();

    return redirect()
        ->route('admin.thong-bao-push.trash')
        ->with('success', 'Khôi phục thông báo thành công.');
}
}
