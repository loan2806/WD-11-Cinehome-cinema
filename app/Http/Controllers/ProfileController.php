<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị giao diện chỉnh sửa hồ sơ người dùng.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ người dùng.
     *
     * Lưu ý nghiệp vụ:
     * - Ngày sinh chỉ được cập nhật 1 lần.
     * - Nếu tài khoản đã có ngày sinh thì không cho ghi đè nữa.
     * - Mục đích: tránh khách hàng đổi ngày sinh nhiều lần để nhận voucher sinh nhật.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        // Nếu người dùng đã có ngày sinh thì không cho cập nhật lại ngày sinh
        if ($user->ngay_sinh) {
            unset($data['ngay_sinh']);
        }

        $user->fill($data);

        // Nếu email thay đổi thì cần xác minh lại email
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Xóa tài khoản người dùng.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}