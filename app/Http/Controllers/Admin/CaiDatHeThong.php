<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaiDatHeThong as ModelCaiDatHeThong;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CaiDatHeThong extends Controller
{
    /**
     * Giao diện cấu hình tham số gốc hệ thống
     */
    public function index(): View
    {
        // Tự động lấy bản ghi cấu hình ID = 1 hoặc khởi tạo mặc định nếu database trống
        $settings = ModelCaiDatHeThong::firstOrCreate(
            ['id' => 1],
            [
                'ten_rap' => 'CineHome Cinema',
                'hotline' => '1900 1234',
                'email' => 'contact@cinehome.vn',
                'dia_chi' => 'Số 1 Cầu Diễn, Bắc Từ Liêm, Hà Nội',
                'thoi_gian_giu_ghe' => 10,
                'so_ve_toi_da_don' => 8,
                'thoi_gian_don_phong' => 15,
                'so_ngay_duoc_dat_ve_truoc' => 7,
                'gia_ngay_thuong' => 75000,
                'gia_cuoi_tuan' => 120000,
                'phu_thu_ghe_vip' => 20000,
                'bat_tat_vnpay' => false,
                'bat_tat_momo' => false,
                'so_phut_truoc_chieu_dang_chieu' => 15,
                'so_ngay_truoc_chieu_sap_ra_mat' => 30, 
            ]
        );

        return view('admin.cai-dat-tham-so-goc.index', compact('settings'));
    }

    /**
     * Cập nhật các tham số vận hành hạt nhân
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = ModelCaiDatHeThong::findOrFail(1);

        $validated = $request->validate([
            'ten_rap' => 'required|string|max:255',
            'hotline' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'dia_chi' => 'required|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            
            'thoi_gian_giu_ghe' => 'required|integer|min:1|max:60',
            'so_ve_toi_da_don' => 'required|integer|min:1|max:20',
            
            'thoi_gian_don_phong' => 'required|integer|min:0|max:120',
            'so_ngay_duoc_dat_ve_truoc' => 'required|integer|min:1|max:90',
            
            'gia_ngay_thuong' => 'required|numeric|min:0',
            'gia_cuoi_tuan' => 'required|numeric|min:0',
            'phu_thu_ghe_vip' => 'required|numeric|min:0',
            
            'vnpay_tmn_code' => 'required_if:bat_tat_vnpay,1|nullable|string|max:50',
            'vnpay_hash_secret' => 'required_if:bat_tat_vnpay,1|nullable|string|max:255',
            
            'momo_partner_code' => 'required_if:bat_tat_momo,1|nullable|string|max:50',
            'momo_access_key' => 'required_if:bat_tat_momo,1|nullable|string|max:255',
            'momo_secret_key' => 'required_if:bat_tat_momo,1|nullable|string|max:255',
            
            'so_phut_truoc_chieu_dang_chieu' => 'required|integer|min:0|max:180',
            'so_ngay_truoc_chieu_sap_ra_mat' => 'required|integer|min:1|max:365',
        ]);

        $validated['bat_tat_vnpay'] = $request->has('bat_tat_vnpay');
        $validated['bat_tat_momo'] = $request->has('bat_tat_momo');

        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $path = $request->file('logo')->store('uploads/branding', 'public');
            $validated['logo'] = $path;
        }

        $settings->update($validated);

        return redirect()->back()->with('success', 'Hệ thống đã ghi nhận và áp dụng tất cả các tham số cài đặt mới.');
    }
}