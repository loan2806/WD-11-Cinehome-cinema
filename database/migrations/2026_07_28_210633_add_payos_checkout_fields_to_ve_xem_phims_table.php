<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Thêm mã giao dịch PayOS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('ve_xem_phims', 'payos_order_code')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->unsignedBigInteger('payos_order_code')
                    ->nullable()
                    ->unique();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Thêm thời gian hết hạn giữ ghế
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('ve_xem_phims', 'thoi_gian_het_han')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->timestamp('thoi_gian_het_han')
                    ->nullable()
                    ->index();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. URL checkout PayOS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('ve_xem_phims', 'payos_checkout_url')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->text('payos_checkout_url')
                    ->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Nội dung QR PayOS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('ve_xem_phims', 'payos_qr_code')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->longText('payos_qr_code')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ve_xem_phims', 'payos_qr_code')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->dropColumn('payos_qr_code');
            });
        }

        if (Schema::hasColumn('ve_xem_phims', 'payos_checkout_url')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->dropColumn('payos_checkout_url');
            });
        }

        if (Schema::hasColumn('ve_xem_phims', 'payos_order_code')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->dropUnique(['payos_order_code']);
                $table->dropColumn('payos_order_code');
            });
        }

        if (Schema::hasColumn('ve_xem_phims', 'thoi_gian_het_han')) {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->dropColumn('thoi_gian_het_han');
            });
        }
    }
};