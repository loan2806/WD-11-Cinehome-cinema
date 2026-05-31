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

        $settings = SystemSetting::query()->orderBy('group')->orderBy('id')->get()->groupBy('group');

        return view('admin.system-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = SystemSetting::query()->get();

        foreach ($settings as $setting) {
            $value = $setting->type === 'boolean'
                ? (string) $request->boolean($setting->key)
                : $request->input($setting->key);

            $setting->update(['value' => $value]);
        }

        return back()->with('success', 'Da luu cau hinh he thong.');
    }

    private function ensureDefaults(): void
    {
        $defaults = [
            ['key' => 'site_name', 'label' => 'Ten he thong', 'value' => 'CineHome', 'group' => 'general', 'type' => 'text'],
            ['key' => 'support_phone', 'label' => 'So dien thoai ho tro', 'value' => '1900 0000', 'group' => 'general', 'type' => 'text'],
            ['key' => 'support_email', 'label' => 'Email ho tro', 'value' => 'support@cinehome.vn', 'group' => 'general', 'type' => 'text'],
            ['key' => 'booking_enabled', 'label' => 'Cho phep dat ve', 'value' => '1', 'group' => 'booking', 'type' => 'boolean'],
            ['key' => 'ticket_cancel_minutes', 'label' => 'So phut duoc huy ve', 'value' => '5', 'group' => 'booking', 'type' => 'number'],
            ['key' => 'refund_percent', 'label' => 'Phan tram hoan tien', 'value' => '50', 'group' => 'booking', 'type' => 'number'],
        ];

        foreach ($defaults as $default) {
            SystemSetting::firstOrCreate(['key' => $default['key']], $default);
        }
    }
}
