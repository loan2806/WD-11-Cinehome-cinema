<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class CaiDatThanhToanController extends Controller
{
    public function edit()
    {
        $this->ensureDefaults();

        $settings = SystemSetting::query()
            ->where('nhom', 'payment')
            ->orderBy('id')
            ->get()
            ->keyBy('khoa');

        return view('admin.cai-dat-thanh-toan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->ensureDefaults();

        $rules = [
            'payment_cash_enabled' => ['nullable', 'boolean'],
            'payment_demo_enabled' => ['nullable', 'boolean'],
            'payment_vnpay_enabled' => ['nullable', 'boolean'],
            'payment_vnpay_tmn_code' => ['nullable', 'string', 'max:255'],
            'payment_vnpay_hash_secret' => ['nullable', 'string', 'max:255'],
            'payment_momo_enabled' => ['nullable', 'boolean'],
            'payment_momo_partner_code' => ['nullable', 'string', 'max:255'],
            'payment_momo_access_key' => ['nullable', 'string', 'max:255'],
            'payment_momo_secret_key' => ['nullable', 'string', 'max:255'],
        ];

        $data = $request->validate($rules);

        foreach ($rules as $key => $rule) {
            $setting = SystemSetting::query()->where('khoa', $key)->first();

            if (! $setting) {
                continue;
            }

            $value = $setting->loai === 'boolean'
                ? (string) $request->boolean($key)
                : ($data[$key] ?? '');

            $setting->update(['gia_tri' => $value]);
        }

        return back()->with('success', 'Đã lưu cấu hình cổng thanh toán.');
    }

    private function ensureDefaults(): void
    {
        $defaults = [
            ['payment_cash_enabled', 'Thanh toán tại quầy', '1', 'boolean'],
            ['payment_demo_enabled', 'Thanh toán giả lập online', '1', 'boolean'],
            ['payment_vnpay_enabled', 'Bật VNPAY', '0', 'boolean'],
            ['payment_vnpay_tmn_code', 'VNPAY TMN Code', '', 'text'],
            ['payment_vnpay_hash_secret', 'VNPAY Hash Secret', '', 'password'],
            ['payment_momo_enabled', 'Bật MoMo', '0', 'boolean'],
            ['payment_momo_partner_code', 'MoMo Partner Code', '', 'text'],
            ['payment_momo_access_key', 'MoMo Access Key', '', 'text'],
            ['payment_momo_secret_key', 'MoMo Secret Key', '', 'password'],
        ];

        foreach ($defaults as [$key, $label, $value, $type]) {
            SystemSetting::putDefault($key, $label, $value, 'payment', $type);
        }
    }
}
