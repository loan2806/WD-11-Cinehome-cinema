<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LienHe;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LienHeController extends Controller
{
    public function index()
    {
        return view('user.lien-he.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
            'chu_de' => ['required', 'string', 'max:255'],
            'noi_dung' => ['required', 'string', 'max:2000'],
        ]);

        $data['nguoi_dung_id'] = auth()->id();

        // User vừa gửi => CHỜ XỬ LÝ
        $data['trang_thai'] = 'cho_xu_ly';

        $lienHe = LienHe::create($data);

        AdminNotificationService::push(
            '🔔 Khiếu nại mới: ' . $lienHe->chu_de,
            "Từ {$lienHe->ho_ten} ({$lienHe->email}): "
                . Str::limit($lienHe->noi_dung, 150),
            'warning',
            route('admin.lien-he.show', $lienHe)
        );

        return redirect()
            ->route('user.lien-he.luu-tru')
            ->with(
                'success',
                'Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất có thể.'
            );
    }
    public function luuTru()
    {
        $lienHes = LienHe::where('nguoi_dung_id', auth()->id())
            ->latest()
            ->paginate(10);

        $contactStats = [
            'total' => LienHe::where('nguoi_dung_id', auth()->id())->count(),

            'pending' => LienHe::where('nguoi_dung_id', auth()->id())
                ->whereIn('trang_thai', ['cho_xu_ly', 'dang_xu_ly'])
                ->count(),

            'replied' => LienHe::where('nguoi_dung_id', auth()->id())
                ->where('trang_thai', 'da_phan_hoi')
                ->count(),

            'closed' => LienHe::where('nguoi_dung_id', auth()->id())
                ->where('trang_thai', 'da_dong')
                ->count(),
        ];

        return view('user.lien-he.luu-tru', compact(
            'lienHes',
            'contactStats'
        ));
    }
}
