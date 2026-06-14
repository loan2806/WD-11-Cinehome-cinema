<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hang_ghes', function (Blueprint $table) {
            // Đánh dấu hàng này có ghép đôi (couple) hay không - sẽ dùng để quyết định ghép cặp trên sơ đồ
            $table->boolean('la_hang_couple')->default(false)->after('ten_hang');
            // Loại ghế mặc định áp dụng cho các ghế mới tạo trong hàng (chỉ là gợi ý, có thể đổi từng ghế)
            $table->foreignId('loai_ghe_mac_dinh_id')
                ->nullable()
                ->after('la_hang_couple')
                ->constrained('loai_ghes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hang_ghes', function (Blueprint $table) {
            $table->dropForeign(['loai_ghe_mac_dinh_id']);
            $table->dropColumn(['la_hang_couple', 'loai_ghe_mac_dinh_id']);
        });
    }
};
