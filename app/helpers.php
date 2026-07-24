<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('coQuyen')) {
    /**
     * Kiểm tra người dùng hiện tại có mã quyền chỉ định hay không
     */
    function coQuyen($maQuyen) {
        $nguoiDung = auth()->user();
        if (!$nguoiDung) return false;

        $vaiTro = $nguoiDung->role ?? $nguoiDung->vai_tro;

        // Quản Trị Viên (Super-admin) mặc định luôn có 100% quyền
        if (in_array($vaiTro, ['super_admin', 'admin', 'quan_ly_he_thong'])) {
            return true;
        }

        $maTranQuyen = Cache::get('ma_tran_phan_quyen_he_thong', []);

        return isset($maTranQuyen[$vaiTro]) && in_array($maQuyen, $maTranQuyen[$vaiTro]);
    }
}

if (!function_exists('coBatKyQuyenNao')) {
    /**
     * Kiểm tra người dùng hiện tại có ít nhất 1 trong danh sách mã quyền
     */
    function coBatKyQuyenNao(array $danhSachMaQuyen) {
        foreach ($danhSachMaQuyen as $maQuyen) {
            if (coQuyen($maQuyen)) return true;
        }
        return false;
    }
}