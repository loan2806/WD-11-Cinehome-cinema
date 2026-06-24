<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cau_hinh_he_thong')) {
            Schema::create('cau_hinh_he_thong', function (Blueprint $table) {
                $table->id();
                $table->string('khoa')->unique();
                $table->text('gia_tri')->nullable();
                $table->string('nhom')->default('general');
                $table->string('nhan')->nullable();
                $table->string('loai')->default('text');
                $table->timestamps();
            });
        }

        $defaults = [
            ['booking_enabled', 'Bat dat ve online', '1', 'booking', 'boolean'],
            ['ticket_cancel_minutes', 'So phut duoc huy ve sau khi dat', '5', 'booking', 'number'],
            ['refund_percent', 'Phan tram hoan tien khi huy ve', '50', 'booking', 'number'],
            ['payment_cash_enabled', 'Thanh toan tai quay', '1', 'payment', 'boolean'],
            ['payment_demo_enabled', 'Thanh toan gia lap online', '1', 'payment', 'boolean'],
            ['payment_vnpay_enabled', 'Bat VNPAY', '0', 'payment', 'boolean'],
            ['payment_vnpay_tmn_code', 'VNPAY TMN Code', '', 'payment', 'text'],
            ['payment_vnpay_hash_secret', 'VNPAY Hash Secret', '', 'payment', 'password'],
            ['payment_momo_enabled', 'Bat MoMo', '0', 'payment', 'boolean'],
            ['payment_momo_partner_code', 'MoMo Partner Code', '', 'payment', 'text'],
            ['payment_momo_access_key', 'MoMo Access Key', '', 'payment', 'text'],
            ['payment_momo_secret_key', 'MoMo Secret Key', '', 'payment', 'password'],
        ];

        foreach ($defaults as [$key, $label, $value, $group, $type]) {
            DB::table('cau_hinh_he_thong')->updateOrInsert(
                ['khoa' => $key],
                [
                    'nhan' => $label,
                    'gia_tri' => $value,
                    'nhom' => $group,
                    'loai' => $type,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_thong');
    }
};
