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

        $lienHe = LienHe::create($data);

        AdminNotificationService::push(
            'Liên hệ mới: ' . $lienHe->chu_de,
            "Từ {$lienHe->ho_ten} ({$lienHe->email}): " . Str::limit($lienHe->noi_dung, 150),
            'warning'
        );

        return back()->with('success', 'Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất có thể.');
    }
}
