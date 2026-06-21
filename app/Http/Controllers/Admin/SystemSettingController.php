<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $this->ensureDefaults();

        $settings = SystemSetting::query()
            ->orderBy('nhom')
            ->orderBy('id')
            ->get()
            ->groupBy('nhom');

        return view('admin.system-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = SystemSetting::query()->get();

        foreach ($settings as $setting) {
            $value = $setting->loai === 'boolean'
                ? (string) $request->boolean($setting->khoa)
                : $request->input($setting->khoa);

            $setting->update(['gia_tri' => $value]);
        }

        return back()->with('success', 'Đã lưu cấu hình hệ thống.');
    }

    private function ensureDefaults(): void
    {
        $defaults = [
            ['site_name', 'Tên hệ thống', 'CineHome', 'general', 'text'],
            ['support_phone', 'Số điện thoại hỗ trợ', '1900 0000', 'general', 'text'],
            ['support_email', 'Email hỗ trợ', 'support@cinehome.vn', 'general', 'text'],
            ['booking_enabled', 'Cho phép đặt vé', '1', 'booking', 'boolean'],
            ['ticket_cancel_minutes', 'Số phút được hủy vé', '5', 'booking', 'number'],
            ['refund_percent', 'Phần trăm hoàn tiền', '50', 'booking', 'number'],
            ['payment_cash_enabled', 'Cho phép thanh toán tại quầy', '1', 'payment', 'boolean'],
            ['payment_demo_enabled', 'Cho phép thanh toán giả lập online', '1', 'payment', 'boolean'],
            ['payment_vnpay_enabled', 'Bật VNPAY', '0', 'payment', 'boolean'],
            ['payment_momo_enabled', 'Bật MoMo', '0', 'payment', 'boolean'],
        ];

        foreach ($defaults as [$key, $label, $value, $group, $type]) {
            SystemSetting::putDefault($key, $label, $value, $group, $type);
        }
    }
}
